'use strict'
var request = require('request');

exports.handler = (event, context) => {
    var mail = event.Records[0].ses.mail;
    var subject = mail.commonHeaders.subject;
    var to_email = mail.commonHeaders.to[0];
    console.log('subject: ' + subject);
    console.log('to: ' + to_email);
    // 送信失敗メールかどうかSubjectで判断
    if (check_subject(subject)) {
        console.log('Delivery Failure');
    
        var url = process.env['AJAX_URL'];
        var api_token = process.env['AJAX_API_TOKEN'];
        var headers = {
            'Content-Type':'text/plain'
        };
        var options = {
            'url': url,
            'method': 'POST',
            'headers': headers,
            form: {
                'qa': 'ajax',
                'qa_operation': 'email_bounce_subject',
                'api_token': api_token,
                'email': to_email
            }
        };
    
        request.post(options, function (err, res, body) {
            if (!err && res.statusCode == 200) {
                context.done(null, body);
            } else {
                context.done('error', err);
            }
        });
    } else {
        console.log('Delivery OK');
    }
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