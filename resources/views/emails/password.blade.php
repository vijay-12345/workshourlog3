
<tr>
   <td class='movableContentContainer' valign='top' style="padding-top: 20px;">
      
      <div class='movableContent'>
         <table width="520" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
               <td align='left'>
                  <div class="contentEditableContainer contentTextEditable">
                     <div class="contentEditable" align='center'>
                        <h2>Hi {{$user['name']}},</h2>
                     </div>
                  </div>
               </td>
            </tr>
            <tr>
               <td height='15'> </td>
            </tr>
            <tr>
               <td align='left'>
                  <div class="contentEditableContainer contentTextEditable">
                     <div class="contentEditable" align='center'>
                        <p  style='text-align:left;color:#999999;font-size:14px;font-weight:normal;line-height:19px;'>
                           <b>Please click below to reset your password for Enuke EMS Portal.</b>
                           <br>
                           <br>
                           
                           <a href="{{ ('http://worklog.yiipro.com/reset_password?'.$user['token']) }}" title="Reset Password">Click here to reset your password</a>
                           <br>
                           <br>
                           
                           <b>Or, copy and paste the following url in your browser:</b>
                           <br>
                           <br>
                           <a href="{{ ('http://worklog.yiipro.com/reset_password?'.$user['token']) }}">{{ ('http://worklog.yiipro.com/reset_password?'.$user['token']) }}</a>
                           <br>
                           <br>
                           
                           <b>It can be safely ignored if you didn't request this.
                           <br>
                           
                           Regards,
                           <br>
                           Team EnukeSoftware
                          </b>
                        </p>
                     </div>
                  </div>
               </td>
            </tr>
            <tr>
               <td height='20'></td>
            </tr>
         </table>
      </div>
   </td>
</tr>