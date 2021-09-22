
<tr style="background-color: #ddd;">
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
                           <a></a>
                           <h2><pre>               </pre>Report of Employee noTask</h2>
                           <br>
                           <br>
                           <table style="height:50px;width:100%;"> 
                              <tr style="background-color: #4CAF50;","color: white;">
                                 <th>Name</th> 
                                 <th>Total Task</th> 
                                 <th>Completed Task</th> 
                                 <th>Total Estimated Time(in hours)</th>
                                 <th>Total Time Spent(in hours)</th>
                              </tr>

                              @foreach($users as $user)
                              <tr>
                                 <td>{{$user['name']}}</td> 
                                 <td style="text-align: center;">{{$user['total_task']}}</td> 
                                 <td style="text-align: center;">{{$user['completed']}}</td> 
                                 <td style="text-align: center;">{{$user['total_estimated_time']}}</td>
                                 <td style="text-align: center;">{{$user['total_time_spent']}}</td> 
                              
                              </tr>


                              @endforeach 
                           </table>     
                           
                           <br>
                           <br>
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