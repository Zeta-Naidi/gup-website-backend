<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!--<![endif]-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <meta name="x-apple-disable-message-reformatting">
  <title></title>
  <style>
    body {
      margin: 0;
      padding: 0;
      width: 100% !important;
    }

    .title {
      background-color: black;
      color: white;
      padding: 10px;
      text-align: center;
    }

    a {
      color: inherit;
    }

    a[x-apple-data-detectors] {
      color: inherit !important;
      text-decoration: none !important;
    }

    img {
      border: 0;
      outline: none;
      line-height: 100%;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    table,
    td {
      mso-table-lspace: 0;
      mso-table-rspace: 0;
    }

    table,
    tr,
    td {
      border-collapse: collapse;
    }

    table.template-container {
      width: 600px;
      margin: 0 auto;
    }


    body,
    td,
    th,
    p,
    div,
    li,
    a,
    span {
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
      mso-line-height-rule: exactly;
    }

    p:first-of-type {
      margin-top: 0 !important;
    }

    p {
      margin-top: 0;
      margin-bottom: 10px;
    }

    p + p {
      margin-top: 10px;
    }

    .gmail-fix {
      display: none !important;
    }

    .sm-right {
      text-align: right;
      margin-left: auto;
    }

    .sm-center {
      text-align: center;
    }

    .sm-padding-left-30 {
      padding-left: 30px;
    }

    .sm-padding-right-20 {
      padding-right: 20px;
    }

    .post-col-left {
      padding-right: 10px;
    }

    .post-col-right {
      padding-left: 10px;
    }

    .sm-col-25 {
      width: 25%;
    }

    .sm-col-33 {
      width: 33%;
    }

    .sm-col-50 {
      width: 50%;
    }

    @media screen and (max-width: 620px) {
      table.template-container {
        width: 320px !important;
        margin: 0 auto;
        white-space: normal;
      }

      .xs-col {
        width: 100% !important;
      }

      .xs-spacing {
        margin: 10px 0 !important;
      }

      .xs-mb-10 {
        margin-bottom: 10px;
      }

      .xs-mb-20 {
        margin-bottom: 20px;
      }

      .xs-center {
        text-align: center;
      }

      .xs-table-center {
        text-align: center;
        margin: 0 auto;
      }

      .xs-padding-lr-0 {
        padding-left: 0 !important;
        padding-right: 0 !important;
      }

      .sm-padding-left-30 {
        padding-left: 0;
      }

      .sm-padding-right-20 {
        padding-right: 0;
      }

      .post-col-left {
        padding-right: 0;
      }

      .post-col-right {
        padding-left: 0;
      }
    }
  </style>
</head>

<body
  style="width: 100% !important; margin: 0; padding: 0; mso-line-height-rule: exactly; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #F5F5F5;; background-position: center; background-repeat: no-repeat; background-size: cover">
<span
  style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">The short summary text that follows the subject line when viewing an email from the inbox. Also known as the Johnson Box or Preview text.</span>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" class="template-body"
       style="padding: 50px 0px; table-layout: fixed;">
  <tbody>
  <tr>
    <td style="vertical-align: top;">
      <table class="template-container">
        <tbody>
        <tr>
          <td>
            <table style="border-collapse: collapse; width: 100%;">
              <tbody class="components__item">
              <tr>
                <td>
                  <table role="presentation" class="component header-2"
                         style="width: 100%; border-collapse: collapse;">
                    <tr>
                      <td style="padding: 30px 35px; background-color: rgb(255, 255, 255); border-radius: 0px;">
                        <table role="presentation" class=" xs-col xs-center"
                               style="width: 100%; border-radius: 3px;">
                          <tbody>
                          <tr>
                            <div class="title">
                              Error Detected in panel mssp at {{isset($host) ? $host : 'host not found'}}
                            </div>
                          </tr>
                          <tr>
                            <td>
                              <div style="margin-bottom: 10px; display: flex; justify-content: center !important;">
                                <img src="https://box.chimpa.eu/images/dontpanic.png" height="250" width="60%">
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div style="display: grid; ">
                                <div style="font-weight: bold">{{'Error Description: '}}</div>
                                {{isset($description) ? $description : 'description exception not found'}}
                              </div>
                              <div style="display: inline-grid;">
                                <div style="font-weight: bold">{{'At File: '}}</div>
                                {{(isset($file) ? $file : 'file exception not found')}}
                              </div>
                              <div style="display: flex;">
                                <div style="font-weight: bold">{{'At Line: '}}</div>
                                {{(isset($line) ? $line : 'line exception not found')}}
                              </div>
                              <div>
                                <div style="font-weight: bold">{{'Backtrace: '}}</div>
                                @foreach($backtrace as $el)
                                  <div>{{$el}}</div>
                                @endforeach
                              </div>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              </tbody>
            </table>
          </td>
        </tr>
        </tbody>
      </table>
    </td>
  </tr>
  </tbody>
</table>
</body>

</html>
