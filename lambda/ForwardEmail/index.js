'use strict';
console.log('Loading function');

let aws = require('aws-sdk');
let s3 = new aws.S3({ apiVersion: '2006-03-01' });
var defaultConfig = {
    fromEmail: "syumatsuyoho@gmail.com",
    forwardToEmail: ["yuichi.shiga@gmail.com"]
};

exports.processMessage = (data, next) => {
   var match = data.emailData.match(/^((?:.+\r?\n)*)(\r?\n(?:.*\s+)*)/m);
   var header = match && match[1] ? match[1] : data.emailData;
   var body = match && match[2] ? match[2] : '';
   data.emailHeader = header;
   data.emailBody = body;

   // Add "Reply-To:" with the "From" address if it doesn't already exists
   if (!/^Reply-To: /m.test(header)) {
       match = header.match(/^From: (.*\r?\n)/m);
       var from = match && match[1] ? match[1] : '';
       if (from) {
           header = header + 'Reply-To: ' + from;
           data.log({
               level: "info",
               message: "Added Reply-To address of: " + from
           });
       } else {
           data.log({
               level: "info",
               message: "Reply-To address not added because " + "From address was not properly extracted."
           });
       }
   }

   // SES does not allow sending messages from an unverified address,
   // so replace the message's "From:" header with the original
   // recipient (which is a verified domain)
   header = header.replace(/^From: (.*)/mg,

   function(match, from) {
      var fromText;

      fromText = 'From: ' + from.replace(/(.*)/, '').trim() + data.config.fromEmail;
      return fromText;
   });
   // Remove the Return-Path header.
   header = header.replace(/^Return-Path: (.*)\r?\n/mg, '');

   // Remove Sender header.
   header = header.replace(/^Sender: (.*)\r?\n/mg, '');

   // Remove all DKIM-Signature headers to prevent triggering an
   // "InvalidParameterValue: Duplicate header 'DKIM-Signature'" error.
   // These signatures will likely be invalid anyways, since the From
   // header was modified.
   header = header.replace(/^DKIM-Signature: .*\r?\n(\s+.*\r?\n)*/mg, '');

   data.emailData = header + body;
   next(null, data);
};

exports.sendMessage = function(data, next) {
   var params = {
       Destinations: data.config.forwardToEmail,
       Source: data.config.fromEmail,
       RawMessage: {
           Data: data.emailData
       }
   };
   data.log({
       level: "info",
       message: "sendMessage: Sending email."
   });
   data.ses.sendRawEmail(params, function(err, result) {
       if (err) {
           data.log({
               level: "error",
               message: "sendRawEmail() returned error.",
               error: err,
               stack: err.stack
           });
           data.context.fail('Error: Email sending failed.');
       } else {
           data.log({
               level: "info",
               message: "sendRawEmail() successful.",
               result: result
           });
           next(null, data);
       }
   });
};

exports.filterSubject = (data, next) => {
    console.log('filterSubject');
    let match = data.emailHeader.match(/^Subject: (.*)\r?\n/m);
    let subject = match && match[1] ? match[1] : null;
    if (subject) {
        if (check_subject(subject)) {
            console.log({
                level: "info",
                message: 'delivery failure'
            });
            match = data.emailBody.match(/^Final-Recipient: rfc822; (.*)\r?\n/m);
            let bounced_email = match && match[1] ? match[1] : null;
            if (bounced_email) {
                // console.log(bounced_email);
                let url = process.env['AJAX_URL'];
                let api_token = process.env['AJAX_API_TOKEN'];
                let headers = {
                    'Content-Type':'text/plain'
                };
                let options = {
                    'url': url,
                    'method': 'POST',
                    'headers': headers,
                    form: {
                        'qa': 'ajax',
                        'qa_operation': 'email_bounce_subject',
                        'api_token': api_token,
                        'email': bounced_email
                    }
                };
                const request = require("request");
                request.post(options, function (err, res, body) {
                    if (!err && res.statusCode == 200) {
                        console.log({
                            level: "info",
                            message: 'email bounced success',
                            bounced_email: bounced_email
                        });
                        next(null, data);
                    } else {
                        data.context.fail('Error: email bounce api failed.');
                    }
                });
            }
            
        }
    }
};

exports.finish = function(data) {
   data.log({
       level: "info",
       message: "Process finished successfully."
   });
   data.context.succeed();
};

exports.handler = (event, context) => {
    //console.log('Received event:', JSON.stringify(event, null, 2));
    var steps = [
      exports.processMessage,
      exports.sendMessage,
      exports.filterSubject
    ];
    var step;
    var currentStep = 0;
    var data = {
      evanet: event,
      context: context,
      config: defaultConfig,
      log: console.log,
      ses: new aws.SES(),
    };
    var nextStep = (err, data) => {
      if (err) {
          data.log({
              level: "error",
              message: "Step (index " + (currentStep - 1) + ") returned error:",
              error: err,
              stack: err.stack
          });
          context.fail("Error: Step returned error.");
      } else if (steps[currentStep]) {
          if (typeof steps[currentStep] === "function") {
              step = steps[currentStep];
          } else {
              return context.fail("Error: Invalid step encountered.");
          }
          currentStep++;
          step(data, nextStep);
      } else {
          // No more steps exist, so invoke the finish function.
          exports.finish(data);
      }

    };
    // Get the object from the event and show its content type
    const bucket = event.Records[0].s3.bucket.name;
    const key = decodeURIComponent(event.Records[0].s3.object.key.replace(/\+/g, ' '));
    const params = {
        Bucket: bucket,
        Key: key
    };
    s3.getObject(params, (err, result) => {
        if (err) {
            data.log({
                level: "error",
                message: "getObject() returned error:",
                error: err,
                stack: err.stack
            });
            return data.context.fail("Error: Failed to load message body from S3.");
        } else {
            // console.log('CONTENT TYPE:', result.ContentType);
            // console.log('BODY:', result.Body.toString());
            data.emailData = result.Body.toString();
            
            nextStep(null, data);
        }
    });
};

var check_subject = (subject) => {
    var result = false;
    var regexs = [
        /Returned mail/i,
        /Non Delivery Notification/i,
        /DELIVERY FAILURE:/i,
        /Undelivered Mail Returned/i,
        /Postmaster notify: see trascript for details/i,
        /Delivery Status Notification \(Failure\)/i,
        /could not send message/i,
        /failure notice/
    ];
    regexs.some( (regex) => {
        if (regex.test(subject)) {
            result = true;
            return true;
        }
    });
    return result;
};