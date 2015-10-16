(function() {

    var moment = require('moment');
    var height = 200;

    
    $.fn.extend({
        history: function(options) {
            options['eltId'] = $(this).attr('id');
            HistoryGraph(options);
            buildSelector(options);
        },
        schedule: function(options) {
            options['eltId'] = $(this).attr('id');
            ScheduleGraph(options);
            buildSelector(options);
        },
        participant: function(options) {
            options['eltId'] = $(this).attr('id');
            ParticipantGraph(options);
            buildSelector(options);
        },
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
        }
    }


    function getYMax(data) {
        var yMax = [];
        for (var i=0; i<data.length; i++) {
            yMax.push(d3.max(data[i]['values'], function(d) { return d.y; }));
        }
        return d3.max(yMax);
    }


    function buildGraph(data, options) {
        settings = {
            'program': null,
            'graphType': null,
            'iconName': null,
            'selector': null,
            'eltId': null
        }
        $.extend(settings, options);
        options = settings; //Need to sync option of the selector

        nv.addGraph(function() {
            chart = nv.models.lineChart()
                .options({
                    transitionDuration: 300,
                    useInteractiveGuideline: true,
                    showLegend: true,
                })
                .margin({"left":30,"right":30,"top":10,"bottom":40})
                .height(height)
                .yScale(d3.scale.sqrt())
                .rightAlignYAxis(true)
                ;
        
            chart.xAxis
                .ticks(d3.time.days, 1)
                .tickFormat(function(d) {
                    date = moment(d).format('YYYY-MM-DD');
                    now = moment().startOf('day').format('YYYY-MM-DD');
                    if (date==now) {
                        return 'today';
                    }
                    return d3.time.format('%d %b %y')(new Date(d));
                })
            ;
            
               var range = getGraphTimeRange(options['selector'], options['iconName']);
            data = fillMissingValues(range, data);

            chart.yAxis
                .tickFormat(function(d) {
                    return d3.format('d')(d);
                })
            ;
            var yMax = getYMax(data);
            chart
                .yDomain([0, yMax])
                .showYAxis(true)
                .showXAxis(true)
            ;

            $("#" + options['eltId']).empty();
            d3.select("#" + options['eltId'])
                .append('svg')
                .datum(data)
                .call(chart)
                .style({ 'height': height })
                .append("svg:image")
                        .attr('x', 0)
                        .attr('y', 0)
                        .attr('width', 20)
                        .attr('height', 20)
                        .attr("xlink:href","/img/" + options['iconName'] + "-icon-20.png");
            return chart;
        });
    }

    function HistoryGraph(options) {
        var url = "/" + options['program'] + "/ProgramHistory/aggregateNvd3.json";
        setDefault(options, 'selector', 'week')
           options['graphType'] = 'history';
           options['iconName'] = 'message';

        getData4Graph(url, options);
    }

    //TODO DRY ajax and timeout and error
    function ScheduleGraph(options) {
        var url = "/" + options['program']+ "/ProgramHome/aggregateNvd3.json";
        setDefault(options, 'selector', 'week')
           options['graphType'] = 'schedule';
           options['iconName'] = 'schedule';

        getData4Graph(url, options);
    }

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

    function ParticipantGraph(options) {
        var url = "/" + options['program']+"/ProgramParticipants/aggregateNvd3.json";
        setDefault(options, 'selector', 'week')
           options['graphType'] = 'participant';
           options['iconName'] = 'participant';

        getData4Graph(url, options);
    }

    function setDefault(options, key, value) {
        if (!(key in options)) {
            options[key] = value;
        }
    }

})();
