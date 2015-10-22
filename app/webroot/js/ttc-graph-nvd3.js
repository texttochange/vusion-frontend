(function() {

    var moment = require('moment'),
        eltIds = {};

    $.fn.extend({
        history: function(options) {
            options['eltId'] = $(this).attr('id');
            HistoryGraph(options);
            buildSelector(options);
        },
        schedule: function(options) {
            options['eltId'] = $(this).attr('id');
            options['yAxisRight'] = false;
            ScheduleGraph(options);
            buildSelector(options);
        },
        participant: function(options) {
            options['eltId'] = $(this).attr('id');
            ParticipantGraph(options);
            buildSelector(options);
        },
        mostActive: function(options) {
        	options['eltId'] = $(this).attr('id');
            BuildMostActiveLists(options);
            buildSelector(options);
        }
    });


    function getGraphTimeRange(timeframe, graphType) {
        var range = [],
            itr = [],
            extent_min = null,
            extent_max = null;


        if (graphType == 'message' || graphType == 'participant') {
            extent_min = moment().subtract(1, timeframe+'s').format("YYYY-MM-DD");
            extent_max = moment().format("YYYY-MM-DD");
        } else {
            extent_min = moment().format("YYYY-MM-DD");
            extent_max = moment().add(1, timeframe+'s').format("YYYY-MM-DD");
        }

           var itr = moment.twix(new Date(extent_min),new Date(extent_max)).iterate("days");
           var range = [];
        while(itr.hasNext()){
            range.push({'x': itr.next().toDate(), 'y': 0})
        }
        return range;
    }


    function fillMissingValues(range, data) {
        parser = d3.time.format("%Y-%m-%d");
        for (var i=0; i<data.length; i++) {
            data[i]['values'].forEach(function(d) {
                d.x = parser.parse(d.x);
            });
            var newData = range.map(function(dayBucket) {
                var exists = _.find(data[i]['values'], {'x': dayBucket.x});
                if (typeof exists != 'undefined') {
                    return exists;
                };
                return dayBucket;
            });
            data[i]['values'] = newData;
        }
        return data;
    }


    function buildSelector(options) {
        $('#'+ options['eltId'] + "-selector").change(function() {
            selecting(this, options)});
    }


    function selecting(elt, options) {
        options['selector'] = $(elt).val();
        switch (options['graphType']) {
            case 'history':
                HistoryGraph(options);
                break;
            case 'schedule':
                ScheduleGraph(options);
                break;
            case 'participant':
                ParticipantGraph(options);
                break;
            case 'most-active':
            	BuildMostActiveLists(options);
            	break;
        }
    }


    function getYMax(data) {
        var yMax = [];
        yMax.push(4);
        for (var i=0; i<data.length; i++) {
            yMax.push(d3.max(data[i]['values'], function(d) { return d.y; }));
        }
        return d3.max(yMax) + 1;
    }


    function buildGraph(data, options) {
        settings = {
            'program': null,
            'graphType': null,
            'iconName': null,
            'selector': null,
            'eltId': null,
            'yAxisRight': true,
            'colors': null,
        }
        $.extend(settings, options);
        options = settings; //Need to sync option of the selector

        var margin = {"left":30,"right":10,"top":10,"bottom":20};
        if (options['yAxisRight']) {
        	margin = {"left":10,"right":30,"top":10,"bottom":20};
        }
        nv.addGraph(function() {
            chart = nv.models.lineChart()
                .options({
                    transitionDuration: 300,
                    useInteractiveGuideline: true,
                    showLegend: true,
                    margin: margin,
                    yScale: d3.scale.sqrt(),
                    rightAlignYAxis: options['yAxisRight'],
                });
            if (options['colors'] != null) {
	            chart.color(options['colors']);
	        }
            chart.xAxis
                .tickSize(3)
                .tickFormat(function(d) {
                    date = moment(d).format('YYYY-MM-DD');
                    now = moment().startOf('day').format('YYYY-MM-DD');
                    if (date==now) {
                        return 'today';
                    }
                    return d3.time.format('%d %b %y')(new Date(d));
                });
            
            var range = getGraphTimeRange(options['selector'], options['iconName']);
            data = fillMissingValues(range, data);
            chart.yAxis
                .tickFormat(function(d) {
                    return d3.format('d')(d);
                });

            var yMax = getYMax(data);
            chart
                .yDomain([0, yMax])
                .showYAxis(true)
                .showXAxis(true);

           	width = $("#" + options['eltId']).width(),
        	height = $("#" + options['eltId']).height(),
            $("#" + options['eltId']).empty();
            d3.select("#" + options['eltId'])
                .append('svg')
                .datum(data)
                .call(chart)
                .attr("width", '100%')
			    .attr("height", '100%')
			    .attr('viewBox','0 0 '+Math.min(width,height)+' '+Math.min(width,height))
			    .attr('preserveAspectRatio','xMinYMin')
			    .append("g")
			    .attr("transform", "translate(" + Math.min(width,height) / 2 + "," + Math.min(width,height) / 2 + ")");
            ;
            eltIds[options['eltId']] = chart;
            return chart;
        });
    }

    
    $(window).resize(function(){
		rescaleGraphWidth();
	});

    function rescaleGraphWidth() {
    	$.each(eltIds, function(eltId, chart) {
    		d3.select("#"+eltId+" svg")
    			.call(chart);
    	});
    };
	

	function getData4Graph(url, options) {
        $.ajax({
            url: url,
            data: (('selector' in options)? {'by': options['selector']}: null),
            type:'GET',
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                data = response['data'];
                buildGraph(data, options);
            }
        });
    }

    function HistoryGraph(options) {
        var url = "/" + options['program'] + "/ProgramHistory/aggregateNvd3.json";
        setDefault(options, 'selector', 'week');
        options['graphType'] = 'history';
        options['iconName'] = 'message';
        options['colors'] = ["#5E6195","#D6CD7A"];

        getData4Graph(url, options);
    }

    //TODO DRY ajax and timeout and error
    function ScheduleGraph(options) {
        var url = "/" + options['program']+ "/ProgramHome/aggregateNvd3.json";
        setDefault(options, 'selector', 'week');
        options['graphType'] = 'schedule';
        options['iconName'] = 'schedule';
        options['colors'] = ["#FEB6B6","#8D2626"];

        getData4Graph(url, options);
    }


    function ParticipantGraph(options) {
        var url = "/" + options['program']+"/ProgramParticipants/aggregateNvd3.json";
        setDefault(options, 'selector', 'week')
        options['graphType'] = 'participant';
        options['iconName'] = 'participant';

        getData4Graph(url, options);
    }


    function BuildMostActiveLists(options) {
    	var url = "/" + options['program']+"/ProgramHistory/mostActive.json";
    	options['graphType'] = 'most-active';
		$.ajax({
			url: url,
			data: (('selector' in options)? {'by': options['selector']}: null),
			dataType: 'json',
			success: function(response) {
				var data = response['data'];
				for (var i = 0; i<data.length; i++) {
					var name = data[i]['name'];
					$("#most-active-" + name).empty();
					$.each(data[i]['values'], function(index, item){
						if (index > 4) {
							return;
						}
						$("#most-active-" + name).append($('<div class="list list-item '+name+'"></div>').append(item['count'] +' - ' + item[name+"-name"]));
					});
				}
			},
			timeout: 10000,
			error: vusionAjaxError
		});
    }

    function setDefault(options, key, value) {
        if (!(key in options)) {
            options[key] = value;
        }
    }

})();
