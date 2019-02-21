var request = require('request');

exports.handler = function(event, context)
{
    var message = event.Records[0].Sns.Message;
    // Messageは文字列なので一度オブジェクトに変換
    var msg_obj = JSON.parse(message);

    if (msg_obj.notificationType == 'Bounce') {
      // bounceオブジェクトを文字列に変換
      var bounce = JSON.stringify(msg_obj.bounce);
      var headers = {
        'Content-Type':'text/plain'
      }
      var options = {
          url: 'https://dev.38qa.net/',
          method: 'POST',
          headers: headers,
          form: {
            'qa': 'ajax',
            'qa_operation': 'email_bounce',
            'api_token': 'ySXFVr7pkrrFd*5oeg19i4AhvhxRSO',
            bounce: bounce,
          },
      };

      request.post(options, function (err, res, body)
      {
          if (!err && res.statusCode == 200)
          {
              context.done(null, body);
          }
          else
          {
              context.done('error', err);
          }
      });
    }
    // context.done(null);
};
