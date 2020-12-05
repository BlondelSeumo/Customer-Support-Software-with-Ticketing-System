<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style type="text/css">
        table td {
            border-collapse: collapse;
        }

        body[dir=rtl] {
            direction: rtl;
            unicode-bidi: embed;
        }
    </style>
</head>

<body style="width: 100%!important; margin: 0; padding: 0;">
@foreach($ticket->latest_replies as $latestReply)
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="100%" style="padding: 15px 0; border-bottom: 1px dotted #c5c5c5;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style=" table-layout:fixed;">
                    <tr>
                        <td valign="top" style="padding: 0 15px 0 15px;width: 40px;">
                            <img alt="user avatar" height="40" width="40" src="{{url($latestReply->user->avatar)}}" style="height: auto; line-height: 100%; outline: none; text-decoration: none; border-radius: 5px;"/>
                        </td>
                        <td width="100%" style="padding: 0; margin: 0;" valign="top">
                            <p style="font-family: 'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif; font-size: 15px; line-height: 18px; margin-bottom: 0; margin-top: 0; padding: 0; color:#1b1d1e;">
                                {{$latestReply->user->display_name}}
                            </p>
                            <p style="font-family: 'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif; font-size: 13px; line-height: 25px; margin-bottom: 15px; margin-top: 0; padding: 0; color:#bbbbbb;">
                                {{$latestReply->created_at_formatted}}
                            </p>
                            <div style="color: #2b2e2f; font-family: 'Lucida Sans Unicode', 'Lucida Grande', 'Tahoma', Verdana, sans-serif; font-size: 14px; line-height: 22px; margin: 15px 0">
                                {!!$latestReply->bodyForEmail()!!}
                                <br/>
                            </div>
                            <p></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <p></p>
@endforeach

<span style="color: #FFFFFF">{{$reference}}</span>
</body>
</html>
