/*!
 * Shopbay visualization widgets library
 *
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */    
var Chart = function(type,selection,config) {
    this.type = type;
    this.selection = selection;
    this.config = config;
    /*console.log(this.type+' '+this.selection,this.config);*/
    this.init = function () {
        /*hide loader if any*/
        $(this.selection+'_loader').hide();
        /*set container width and height*/
        $(this.selection).css({width:this.config.width,height:this.config.height}); 
        /*reset active filter*/
        $(this.selection).parent().find('.chart-filter li').removeClass('active'); 
        if (this.config.filter!=undefined)
            $(this.selection).parent().find('.chart-filter li.'+this.config.filter.options.value).addClass('active');
    };
    this.render = function (){
        this.init();
        var chart = instantiate(this.type,[this.selection,this.config]);
        chart.render();
    }; 
};

var GrowthChart = function (selection, config) {
    this.render = function() {
        /*reset chart*/
        $(selection+' .growth-area').remove();
        /*draw chart */
        var containerId = $(selection).parent().attr('id');
        $.each(config.data,function(i,d){
            var subContainerId = containerId+'_'+i;
            $(selection).append('<div id="'+subContainerId+'" class="growth-area"></div>'); 
            if (!mobiledisplay())
                $('#'+subContainerId).css({width:(100/config.data.length)+'%'}); 
            $('#'+subContainerId).append('<div class="data"></div>'); 
            $('#'+subContainerId+' .data').append('<div class="title">'+d.title+'</div>'); 
            $('#'+subContainerId+' .data').append('<div class="total">'+d.total+'</div>'); 
            $('#'+subContainerId).append('<div class="arrow"></div>'); 
            if (d.growth>0)
                $('#'+subContainerId+' .arrow').append('<i class="fa fa-caret-up" style="color:lightgreen"></i>'); 
            if (d.growth<0)
                $('#'+subContainerId+' .arrow').append('<i class="fa fa-caret-down" style="color:red"></i>'); 
            $('#'+subContainerId).append('<div class="subscript">'+d.subscript+'</div>'); 
        });
    };
};

var LineChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection+' svg').remove();
        /*start convert x-axis into correct values */
        var convertXIntoDateValues = function (array, date) {
            for (var i = 0; i < array.length; i++) {
                var convertedDate = Date.createFromMysql(array[i].x);
                if (convertedDate.toString() === date.toString()) {
                    return {x:convertedDate, y: parseFloat(array[i].y)};
                }
            }  
            return false;
        };
        var dateRange = chartDateRange(this.config.filter.options.value);
        /*Data values conversion, insert empty date with zero values */
        $.each(this.config.data,function(i,d){
            var conversion = [];
            $.each(dateRange,function(j,date){
                /*scan through date range*/
                var obj = convertXIntoDateValues(d.values,date);
                if (obj!=false)
                    conversion.push(obj);
                else
                    conversion.push({x:date, y: 0});
            });
            d.values = conversion;
        });
        /*end date-based x-axis */

        /*draw chart */
        var chart = nv.models.lineChart()
        .options({
            margin: {top: this.config.margin.top, right: this.config.margin.right, bottom: this.config.margin.bottom, left: this.config.margin.left},
            width: $(this.selection).width(),
            height: $(this.selection).height(),
            x: function(d){return d.x;},
            y: function(d){return d.y;},
            showLegend: config.showLegend,
            showXAxis: true,
            showYAxis: true,
            transitionDuration: 250
        })
        ;

        var yTooltipFormat = this.config.yAxisFormat;
        var tooltip = function(key, x, y, e, graph) {
            return '<h3>' + key + '</h3>' +
                   '<p>' +  d3.format(yTooltipFormat)(y) + ' at ' + x + '</p>'
        };    

        chart.tooltipContent(tooltip);

        chart.xScale(d3.time.scale());
        chart.xDomain(d3.extent(dateRange));

        chart.yScale(d3.scale.linear());
        chart.yDomain([0,d3.max(config.data[0].values,function(d){return d.y;})]);

        chart.xAxis
            .showMaxMin(false)
            .staggerLabels(true)
            .axisLabel(config.xAxisLabel)
            .axisLabelDistance(25)
            ;

        chart.yAxis
            .showMaxMin(false)
            .axisLabelDistance(25)
            .axisLabel(config.yAxisLabel)
            ;

        /*set axis tick format*/
        if (this.config.xAxisFormat!=null)
            chart.xAxis.tickFormat(parseAxisFormat(this.config.xAxisFormat));
        if (this.config.yAxisFormat!=null)
            chart.yAxis.tickFormat(parseAxisFormat(this.config.yAxisFormat));
        
        var svg =  d3.select(this.selection).append('svg')
            .attr('width',$(this.selection).width())
            .attr('height',$(this.selection).height())
            .attr('id',config.svgId)
            .classed(config.svgCssClass,true)
            ;

        svg.datum(config.data)
            .call(chart);

        nv.utils.windowResize(chart.update);

        chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
        
    };
};

var HistoricalBarChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection+' svg').remove();
        /*start convert x-axis into correct values */
        var convertXIntoDateValues = function (array, date) {
            for (var i = 0; i < array.length; i++) {
                var convertedDate = Date.createFromMysql(array[i].x);
                if (convertedDate.toString() === date.toString()) {
                    return {x:convertedDate, y: parseFloat(array[i].y)};
                }
            }  
            return false;
        };
        var dateRange = chartDateRangePadding(this.config.filter.options.value,1);
        /*Data values conversion, insert empty date with zero values */
        $.each(this.config.data,function(i,d){
            var conversion = [];
            $.each(dateRange,function(j,date){
                /*scan through date range*/
                var obj = convertXIntoDateValues(d.values,date);
                if (obj!=false)
                    conversion.push(obj);
                else
                    conversion.push({x:date, y: 0});
            });
            d.values = conversion;
        });
        /*end date-based x-axis */

        /*draw chart */
        var chart = nv.models.historicalBarChart()
        .options({
            margin: {top: this.config.margin.top, right: this.config.margin.right, bottom: this.config.margin.bottom, left: this.config.margin.left},
            width: $(this.selection).width(),
            height: $(this.selection).height(),
            x: function(d){return d.x;},
            y: function(d){return d.y;},
            color: randomD3ColorRange(20),
            transitionDuration: 250
        })
        ;

        chart.xScale(d3.time.scale());
        chart.xDomain(d3.extent(dateRange));

        chart.yScale(d3.scale.linear());
        chart.yDomain([0,d3.max(config.data[0].values,function(d){return d.y;})]);

        chart.showXAxis(true);

        chart.xAxis
            .showMaxMin(false)
            .staggerLabels(true)
            .axisLabel(config.xAxisLabel)
            .axisLabelDistance(25)
            ;

        chart.yAxis
            .showMaxMin(false)
            .axisLabelDistance(25)
            .axisLabel(config.yAxisLabel)
            ;
            
        /*set axis tick format*/
        if (this.config.xAxisFormat!=null)
            chart.xAxis.tickFormat(parseAxisFormat(this.config.xAxisFormat));
        if (this.config.yAxisFormat!=null)
            chart.yAxis.tickFormat(parseAxisFormat(this.config.yAxisFormat));
            
        var svg =  d3.select(this.selection).append('svg')
            .attr('width',$(this.selection).width())
            .attr('height',$(this.selection).height())
            .attr('id',config.svgId)
            .classed(config.svgCssClass,true)
            ;

        svg.datum(this.config.data)
            .transition().duration(0)
            .call(chart);

        nv.utils.windowResize(chart.update);

        chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
        
    };
};

var LinePlusBarChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection+' svg').remove();
        /*Data values conversion, insert empty date with zero values */
        var dateRange = chartDateRangePadding(this.config.filter.options.value,1);/*indicate padding*/
        $.each(this.config.data,function(i,d){
            var conversion = [];
            $.each(dateRange,function(j,date){
                /*scan through date range*/
                $.each(d.values,function(k,value){
                    if (Date.createFromMysql(value[0]).toString() === date.toString()) 
                        conversion.push([date,value[1]]);                
                    else
                        conversion.push([date,0]);       
                });
            });
            d.values = conversion;
        });
        /*end date-based x-axis */
        
        var datatset = this.config.data.map(function(series) {
                    series.values = series.values.map(function(d) { return {x: d[0], y: d[1] } });
                    return series;
                });

        var chart = nv.models.linePlusBarChart()
        .options({
            margin: {top: this.config.margin.top, right: this.config.margin.right, bottom: this.config.margin.bottom, left: this.config.margin.left},
            width: $(this.selection).width(),
            height: $(this.selection).height(),
            x: function(d,i) { return i },
            color: randomD3ColorRange(20),
            showLegend: this.config.showLegend,
        })
        ;        

        chart.xAxis
            .showMaxMin(false)
            //.staggerLabels(true)
            .axisLabel(config.xAxisLabel)
            .axisLabelDistance(25)
            .tickFormat(function(d) {
                var dx = datatset[0].values[d] && datatset[0].values[d].x || 0;
                return dx ? d3.time.format('%x')(new Date(dx)) : '';
            })
            ;
            
        var maxValue = function (array) {
            var max = 0;
            $.each(array,function(i,d){
                if (d.y > max)
                    max = d.y;
            });
            return max;
        };
                
        var barMaxValue = maxValue(datatset[0].values);/*dataset[0] should contain bar values */
        if (barMaxValue==1){
            chart.bars.yDomain([0,4]);
        }
        var lineMaxValue = maxValue(datatset[1].values);/*dataset[1] should contain lines values */
        var multiplier = Math.ceil(Math.ceil(lineMaxValue / barMaxValue) / barMaxValue);/*calculate multiplier factor to have correct proportion*/
        chart.lines.yDomain([0,multiplier*lineMaxValue]);
        chart.lines.yScale(d3.scale.linear());
        
        chart.y1Axis
            .showMaxMin(false)
            .tickFormat(d3.format(',f'));

        var currencySymbol = this.config.currencySymbol;
        
        chart.y2Axis
            .showMaxMin(false)
            .tickFormat(function(d) { return currencySymbol + d3.format(',.2f')(d) });


        chart.bars.forceY([0]).padData(false);
        /*chart.lines.forceY([0]);*/

        var svg =  d3.select(this.selection).append('svg')
            .attr('width',this.config.width)
            .attr('height', this.config.height)
            .attr('id',this.config.svgId)
            .classed(this.config.svgCssClass,true)
            ;

        svg.datum(datatset)
            .transition().duration(500).call(chart);       
    
        nv.utils.windowResize(chart.update);

        chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
        
    }
}

var PieChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection+' svg').remove();
        
        var chart = nv.models.pieChart()
        .options({
            margin: {top: this.config.margin.top, right: this.config.margin.right, bottom: this.config.margin.bottom, left: this.config.margin.left},
            width: $(this.selection).width(),
            height: $(this.selection).height(),
            x: function(d){return d.key;},
            y: function(d){return d.value;},
            color: randomD3ColorRange(20),
            showLegend: this.config.showLegend,
        })
        ;        

        /*calculate total to compute percentage of each component */
        var total = 0;
        $.each(this.config.data,function(i,d){
            total += parseFloat(d.value);
        });
        
        var tooltip = function(key, y, e, graph) {
            return '<h3>' + key + '</h3>' +
                   '<p>' +  parseFloat(y).toFixed(0) +  ' (' + parseFloat((y/total)*100).toFixed(2) +'%)</p>'
        };    

        chart.tooltipContent(tooltip);        
                  
        var svg =  d3.select(this.selection).append('svg')
            .attr('width',this.config.width)
            .attr('height', this.config.height)
            .attr('id',this.config.svgId)
            .classed(this.config.svgCssClass,true)
            ;

        svg.datum(this.config.data)
            .call(chart);         
         
        nv.utils.windowResize(chart.update);
 
        chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
        
    };
};

var VerticalBarChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection+' svg').remove();
        
        var chart = nv.models.discreteBarChart()
        .options({
            margin: {top: this.config.margin.top, right: this.config.margin.right, bottom: this.config.margin.bottom, left: this.config.margin.left},
            width: $(this.selection).width(),
            height: $(this.selection).height(),
            x: function(d){return d.label;},
            y: function(d){return d.value;},
            color: randomD3ColorRange(20),
            showXAxis: true,
            showYAxis: true,
            staggerLabels: false,
            tooltips: true,
            showValues: true,
            transitionDuration: 250,
        })
        ;     
         
        chart.xAxis
            .showMaxMin(false)
            .staggerLabels(true)
            .axisLabel(this.config.xAxisLabel)
            .axisLabelDistance(25)
            ;
            
        chart.yAxis
            .showMaxMin(false)
            .axisLabelDistance(25)
            .axisLabel(this.config.yAxisLabel)
            ;            
         
        var svg =  d3.select(this.selection).append('svg')
            .attr('width',this.config.width)
            .attr('height', this.config.height)
            .attr('id',this.config.svgId)
            .classed(this.config.svgCssClass,true)
            ;

        svg.datum(this.config.data)
            .call(chart);         
    
        nv.utils.windowResize(chart.update);
        
    };
};

var CounterChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*reset chart*/
        $(this.selection).addClass('counter-chart');
        $(this.selection).css({width:this.config.width,height:this.config.height}); 
        $(this.selection+' .counter-area').remove();
        /*draw chart */
        var containerId = $(this.selection).parent().attr('id');
        $.each(this.config.data,function(i,d){
            var subContainerId = containerId+'_'+i;
            $(selection).append('<div id="'+subContainerId+'" class="counter-area"></div>'); 
            /*
            if (!mobiledisplay())
                $('#'+subContainerId).css({width:((100/config.data.length) - 3)+'%'}); 
            */
            for (var prop in d){
                if (d[prop]!=undefined)
                    $('#'+subContainerId).append('<div class="'+prop+'">'+d[prop]+'</div>');        
            }
        });        

    };
};

var TabularChart = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {
        /*console.log('TabularChart config',this.config);*/
        /*reset chart*/
        $(this.selection+' table').remove();
        $(this.selection).addClass('tabular-chart');
        $(this.selection).css({width:this.config.width,height:this.config.height}); 
        /*draw chart */
        var containerId = $(this.selection).parent().attr('id');
        var tableId = containerId+'_table';
        $(this.selection).append('<table id="'+tableId+'"><thead></thead><tbody></tbody></table>'); 
        $.each(this.config.data,function(i,row){
            var rowHtml = '<tr>';
            for (var col in row){
                if (row[col]!=undefined){
                    if (i==0)
                        rowHtml += '<th class="'+col+'">'+row[col]+'</th>';     
                    else
                        rowHtml += '<td class="'+col+'">'+row[col]+'</td>';        
                }
            }
            rowHtml += '</tr>';
            if (i==0)/*first row*/
                $('#'+tableId+' thead').append(rowHtml);
            else
                $('#'+tableId+' tbody').append(rowHtml);
        });  
        if (this.config.data.length==0){
            $('#'+tableId+' tbody').append('<div class="empty-area">'+this.config.emptyText+'</div>');
        }

    };
};

var ChartContainer = function (selection, config) {
    this.selection  = selection;
    this.config  = config;
    this.render = function () {       
        /*console.log('ChartContainer config',this.config);*/
        /*set container width and height*/
        var containerId = this.selection;        
        var columnWidth = this.config.columnWidth;        
        var widgetCount = this.config.data.length;
        $(containerId).css({width:this.config.width,height:this.config.height}); 
        /*draw widgets */
        $.each(this.config.data,function(idx,data){
            /*console.log('ChartContainer widget data',data);*/
            var suffix = data.config.filter.shop!=undefined?'_'+data.config.filter.shop:'';
            if (data.config.filter.currency!=undefined)
                suffix += '_'+data.config.filter.currency;
            var widgetContainerId = 'widget_'+data.id+suffix;
            $(containerId).append('<div id="'+widgetContainerId+'" class="'+data.id+' chart-container"></div>'); 
            $('#'+widgetContainerId).append('<div class="chart-name">'+(data.name!=null?data.name:'')+'</div>'); 
            if (data.filterBar!=null){
                $('#'+widgetContainerId).append('<div class="chart-filter">'+data.filterBar+'</div>'); 
            }
            if (!mobiledisplay()){
                if (columnWidth==undefined)
                    $('#'+widgetContainerId).css({width:(100/widgetCount)+'%'}); 
                else 
                    $('#'+widgetContainerId).css({width:columnWidth[idx]}); 
            }
            $('#'+widgetContainerId).append('<div id="'+widgetContainerId+'_canvas" class="chart-canvas"></div>'); 
            new Chart(data.type,data.selection,data.config).render();
        });  

    };
};

/* --------Start Helper library--------*/
Date.createFromMysql = function(mysql_string){ 
   if(typeof mysql_string === 'string'){
      var t = mysql_string.split(/[- :]/);
      /*when t[3], t[4] and t[5] are missing they defaults to zero*/
      return new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);          
   }
   return null;   
}
/*Setup date range array based on query */
var chartDateRange = function(offset) {
    var offsetDay =  offset - 1;
    var today = new Date(); 
    today.setHours(0,0,0,0);
    var dateArray = [];
    for(i=offsetDay;i>=0;i--) { 
        dateArray.push(d3.time.day.offset(today, -i));
    }
    return dateArray;
};
var chartDateRangePadding = function(offset, padding) {
    var offsetDay =  offset - 1;
    if (padding!=undefined)
        offsetDay +=  2;
    var today = new Date(); 
    today.setHours(0,0,0,0);
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate()+1);
    var dateArray = [];
    for(i=offsetDay;i>=0;i--) { 
        dateArray.push(d3.time.day.offset(tomorrow, -i));
    }
    return dateArray;
};
var randomNumber = function (ceiling){ return Math.floor(Math.random() * ceiling); };
var randomD3Color = function () { return d3.scale.category20().range()[randomNumber(20)];};
var randomD3ColorRange = function (n) {
    var d3color = d3.scale.category20().range();
    var arr = [];
    for (var i = 0; i < n; i++ ) 
        arr.push(d3color[randomNumber(20)]);
    return arr;
};
var randomColor = function () {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[randomNumber(16)];
    }
    return color;
};
/* Dynamic instantiation
 * var foo = instantiate(Array, [arg1, arg2, ...]);
 * Instead of: var foo = instantiate("Array", [arg1, arg2, ...]);
 * @param {type} className
 * @param {type} args
 * @returns {instantiate.o|instantiate.f}
 */
function instantiate(className, args) {
    var o, f, c;
    c = window[className]; // get reference to class constructor function
    f = function(){}; // dummy function
    f.prototype = c.prototype; // reference same prototype
    o = new f(); // instantiate dummy function to copy prototype properties
    c.apply(o, args); // call class constructor, supplying new object as context
    o.constructor = c; // assign correct constructor (not f)
    return o;
}
function parseAxisFormat(format)
{
    if (format.lastIndexOf('date~',0)===0){
        format = format.substring(5);/*discard date~*/
        return function(d) {
                    return d3.time.format(format)(d);
                };            
    }
    else {
        return d3.format(format);
    }    
}
/* --------End Helper library--------*/    