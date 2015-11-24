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


    function getGraphTimeRange(timeframe, statsType, momentComparingFormat, iterateOn, xMin) {
        var range = [],
            itr = [],
            extent_min = null,
            extent_max = null;


        if (statsType == 'history' || statsType == 'participants') {
            if (timeframe == 'ever') {
                extent_min = moment(xMin).subtract(1, 'days').format(momentComparingFormat)
            } else {
                extent_min = moment().subtract(1, timeframe+'s').format(momentComparingFormat);
            }
            extent_max = moment().format(momentComparingFormat);
        } else if (statsType == 'schedules') {
            extent_min = moment().format(momentComparingFormat);
            extent_max = moment().add(1, timeframe+'s').format(momentComparingFormat);
        }

        var itr = moment.twix(new Date(extent_min),new Date(extent_max)).iterate(iterateOn);
        var range = [];
        while(itr.hasNext()) {
            range.push({'x': itr.next().toDate(), 'y': 0})
        }
        return range;
    }


    function fillMissingValues(range, data, iterateOn, cumulative) {
        parser = d3.time.format("%Y-%m-%d");
        iterateOn = iterateOn.substring(0, iterateOn.length - 1);
        for (var i=0; i<data.length; i++) {
            data[i]['values'].forEach(function(d) {
                d.x = parser.parse(d.x);
            });
            var newData = range.map(function(timeBucket) {
                var exists = _.filter(data[i]['values'], function(elt) {
                    return moment(elt.x).isSame(timeBucket.x, iterateOn);
                });
                if (exists.length == 1) {
                  return exists[0];
                } else if (exists.length > 1) {
                    return _.reduce(exists, function(total, n) {
                                if (cumulative) {
                                    total.y = total.y + n.y;
                                } else if (total.y < n.y) {
                                    total.y = n.y;
                                }
                                return total;
                            }, {'x': timeBucket.x, 'y': 0});
                }
                return timeBucket;
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

    function getXMin(data) {
        var xMin = [];
        for (var i=0; i<data.length; i++) {
            min = d3.min(data[i]['values'], function(d) { return d.x; });
            if (min !== undefined) {
                xMin.push();
            }
        }
        return d3.min(xMin);
    }

    function buildGraph(data, options) {
        settings = {
            'program': null,
            'graphType': null,
            'statsType': null,
            'iconName': null,
            'selector': null,
            'eltId': null,
            'yAxisRight': true,
            'colors': null,
            'cumulative': true,
        }
        $.extend(settings, options);
        options = settings; //Need to sync option of the selector

        switch (options['selector']) {
            case 'year':
                var iterateOn = 'months',
                    timeTickFormat = '%b %Y',
                    momentComparingFormat = 'YYYY-MM',
                    currentLabel = 'this month';
                break;
            default:
                var iterateOn = 'days',
                    timeTickFormat = '%d %b %y',
                    momentComparingFormat = 'YYYY-MM-DD',
                    currentLabel = 'today';
                break;
        }

        var margin = {"left": 30,"right": 10,"top": 10,"bottom": 20};
        if (options['yAxisRight']) {
            margin = {"left": 30,"right": 30,"top": 10,"bottom": 20};
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
                .ticks(3)
                .tickFormat(function(d) {
                    date = moment(d).format(momentComparingFormat);
                    now = moment().startOf('day').format(momentComparingFormat);
                    if (date==now) {
                        return currentLabel;
                    }
                    return d3.time.format(timeTickFormat)(new Date(d));
                });
            
            var range = getGraphTimeRange(options['selector'], options['statsType'], momentComparingFormat, iterateOn, getXMin(data));
            data = fillMissingValues(range, data, iterateOn, options['cumulative']);
            chart.yAxis
                .tickFormat(function(d) {
                    if (d < 1000) {
                        return d3.format('d')(d);
                    }
                    return d3.format('.3s')(d);
                })
                .ticks(3);

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
        data = {'stats_type': options['statsType']}
        if ('selector' in options) {
            data['for'] =  options['selector'];
        }
        $.ajax({
            url: url,
            data: data,
            type:'GET',
            contentType: 'application/json;charset=utf-8',
            dataType: 'json',
            success: function(response) {
                data = response['data'];
                buildGraph(data, options);
            }
        });
    }

    function HistoryGraph(options) {
        var url = "/" + options['program'] + "/ProgramAjax/getStats.json";
        setDefault(options, 'selector', 'week');
        options['graphType'] = 'history';
        options['iconName'] = 'message';
        options['colors'] = ['#5E6195', '#D6CD7A'];
        options['statsType'] = 'history';

        getData4Graph(url, options);
    }

    //TODO DRY ajax and timeout and error
    function ScheduleGraph(options) {
        var url = "/" + options['program']+ "/ProgramAjax/getStats.json";
        setDefault(options, 'selector', 'week');
        options['graphType'] = 'schedule';
        options['iconName'] = 'schedule';
        options['colors'] = ['#FF8101','#FFB701'];
        options['statsType'] = 'schedules';

        getData4Graph(url, options);
    }


    function ParticipantGraph(options) {
        var url = "/" + options['program']+"/ProgramAjax/getStats.json";
        setDefault(options, 'selector', 'week')
        options['graphType'] = 'participant';
        options['iconName'] = 'participant';
        options['colors'] = ['#539279','#C16E86'];
        options['statsType'] = 'participants';
        options['cumulative'] = false;

        getData4Graph(url, options);
    }


    function BuildMostActiveLists(options) {
        var url = "/" + options['program']+"/ProgramAjax/getStats.json";
        options['graphType'] = 'most-active';
        data = {'stats_type': 'top_dialogues_requests'}
        if ('selector' in options) {
            data['for'] = options['selector'];
        }
        $.ajax({
            url: url,
            data: data,
            dataType: 'json',
            success: function(response) {
                var data = response['data'];
                for (var i = 0; i<data.length; i++) {
                    var name = data[i]['name'];
                    $("#most-active-" + name).empty();
                    if (data[i]['values'].length == 0) {
                        $("#most-active-" + name).append($('<div class="list list-item no-message"></div>').append('none received message'));
                    }
                    $.each(data[i]['values'], function(index, item){
                        if (index > 4) {
                            return;
                        }
                        $("#most-active-" + name).append($('<div class="list list-item '+name+'"></div>').append(item['count'] +' - ' + item[name+"-name"]));
                    });
                }
            },
        });
    }

    function setDefault(options, key, value) {
        if (!(key in options)) {
            options[key] = value;
        }
    }

})();
