<div style="padding-left:20px;">
    <table style="font-size: 1.2em;">  
        <tr>  
              <td>
                  <span style="float:right;"><?php echo Sii::t('sii','{app} Team',array('{app}'=>app()->name));?></span>
              </td>  
        </tr>  
    </table>  
</div>

<div style="padding-left:20px;padding-top:20px;">
    <table style="font-size: 0.85em;">
        <tr>
            <td>
                <span><?php echo Sii::t('sii','Note: Please do not reply to this email; For any support issue, please contact us at <em>{email}</em>',['{email}'=>Config::getSystemSetting('email_contact')]);?></span>
            </td>
        </tr>
    </table>
</div>

<div style="clear:both"></div>