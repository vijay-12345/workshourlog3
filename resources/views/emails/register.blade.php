
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
                           <b>Thanks for signing up with Enuke EMS Portal!.</b>
                           <br>
                           <br>
                           <b>You must follow this link to activate your account:!.</b>
                           <br>
                           <br>
                            <a href="{{ ('http://worklog.yiipro.com/account_verify?'.$user['token']) }}">{{ ('http://worklog.yiipro.com/account_verify?'.$user['token']) }}</a>
                           <br>
                           <br>
                           
                           <b>Having Fun with your Tasks.
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