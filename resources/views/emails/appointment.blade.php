<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title></title>
  <style>
    table,
    td,
    div,
    h1,
    p {
      font-family: Verdana, Arial, sans-serif;
    }

    @media only screen and (max-width: 605px) {
      .mob-width {
        width: 100%;
      }
    }
  </style>
</head>

<body style="margin:0;padding:0;">
  <table role="presentation"
    style="width: 831px;border-collapse:collapse;border:0;border-spacing:0;background-color: #F5F5F5;" align="center">
    <tr>
      <td align="center" style="padding:0;    padding-top: 41px;">
        <table role="presentation" class="mob-width"
          style="width:600px;border-collapse:collapse;border-spacing:0;text-align:left;background-color: #fff;">
          <tr>
            <td style="padding: 35px 35px 35px 35px;color:#153643;">
              <table role="presentation"
                style="width:100%;border-collapse:collapse;border:0;border-spacing:0;/*box-shadow: 0 5px 50px rgb(0 0 0 / 31%);*/padding: 50px;/*display: inline-block;*/ /*margin-top: -90px;*/z-index: 10;position: relative;border-radius: 12px;background-repeat: no-repeat;">
                <tr>
                  <td>
                    <h1
                      style="font-size:16px;margin: 0;font-weight:700;font-family:Verdana,Arial,sans-serif;text-align: center;color:#2B4576">
                      Meeting Request From {{$user['first_name']??''}}- Origination Boost App Notification</h1>
                      <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Requester Name: {{$user['first_name']??''}} {{$user['last_name']??''}}</p>
                      <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Requester Email: {{$user['email']??''}}</p>
                      <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Requester Phone: {{$user['phone']??''}}</p>
                      <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Preffered Date: {{$data['preferred_date']??''}}</p>
                    <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Preffered Time: {{$data['preferred_time']??''}}</p>
                    <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Topic: {{$data['topic']??''}}</p>
                    <p
                      style="margin:0;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color:#545454;margin: 15px 0px;">
                      Body: {{$data['message']??''}}</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td align="center">
        <p
          style="margin:23px 0 21px;font-size:14px;line-height:24px;font-family:Verdana,Arial,sans-serif;color: rgb(0 0 0 / 54%);">
          Copyright © 2023 - PMR Loans.</p>
      </td>
    </tr>
  </table>
</body>

</html>