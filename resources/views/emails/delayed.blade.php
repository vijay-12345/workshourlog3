

<table id="customers" style="background-color: #ddd;">
<tr>
   <td class='movableContentContainer' valign='top' style="padding-top: 20px;">
      
      <div class='movableContent'>
         <table width="520" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
               <td align='left'>
                  <div class="contentEditableContainer contentTextEditable">
                     <div class="contentEditable" align='center'>
                        
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
                           <h2><pre>               </pre>Report of delayed task of Employees</h2>
                           <br>
                           <br>
                        </p>   
                           <table style="height:50px;width:100%;">
                              <tr style="background-color: #4CAF50;","color: white;">
                                 <th>Name</th> 
                                 <th>Total Task</th> 
                                 <th>Delayed Task</th>
                              </tr>
                              @foreach($users as $user)
                              <tr>
                                 <td>{{$user['user_name']}}</td> 
                                 <td style="text-align: center;">{{$user['total_task']}}</td> 
                                 <td style="text-align: center;">{{$user['delayed_task']}}</td> 
                              </tr>
                              @endforeach
                           </table>     
                           
                           <br>
                           <br>
                        
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
</table>
