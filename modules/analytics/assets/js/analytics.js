/*!
 * Shopbay analytics library
 *
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */  
function urldecode(str) {
    if (typeof str != "string")        
        return str;
    return decodeURIComponent(str.replace(/\+/g, ' '));
}
function refreshdashboard(getcharturl){
   $.get(getcharturl, function(data) {
        $.each(data,function(idx,data){
            new Chart(data.config.svgCssClass,data.selection,data.config).render();
        });
        enablesectionbuttons();
   })
   .error(function(XHR) { error(XHR);  }); 
}
function query(id,type,selection,filter,shop,currency){
   $(urldecode(selection)+'_loader').show();
   $.get('/analytics/management/chart/id/'+id+'/type/'+type+'/selection/'+selection+'/filter/'+filter+'/shop/'+shop+'/currency/'+currency, function(data) {
       new Chart(type,data.selection,data.config).render();
       $(urldecode(selection)+'_loader').hide();
   })
   .error(function(XHR) { error(XHR);  }); 
}