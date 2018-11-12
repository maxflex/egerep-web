var opacity = "0.2";

/*
 * options
 * -> places
 * -> placesHash
 * -> saveCallback
 */
function SVGMap(options) {
    this.options = options;
    this.selected = {};
    this.relSS = false;//relation of station and stroke:defined relationsheep between stations and strokes around it(ex.: there's stroke around kurskaya x2 and chkalovskaya)
}

SVGMap.prototype = {
    init: function() {
		var iframeObj = $('#'+this.options.iframeId);

		var iframeWidth = parseInt(iframeObj.attr('data-width'));

		var svgobject = document.getElementById(this.options.iframeId);
		var svgdom = jQuery(svgobject.contentDocument);

		var iframeWidth = parseInt(iframeObj.attr('data-width'));
		var MapRealWidth = parseInt($('#svg2', svgdom).attr('width'));
		var MapRealHeight = parseInt($('#svg2', svgdom).attr('height'));
		var MapWidthPart = iframeWidth/MapRealWidth;

		$('#svg2', svgdom).attr("width", iframeWidth);
		$('#svg2', svgdom).attr("height", MapWidthPart*MapRealHeight);

		iframeObj.css('height', MapWidthPart*MapRealHeight);
		iframeObj.css('width', iframeWidth);
		iframeObj.css('overflow', 'hidden').show();

		this.options.unions = JSON.parse($(svgdom).find('rel#unions').text());
		this.options.lines_stations = JSON.parse($(svgdom).find('rel#lines_stations').text());

		this.options.placesHash = {};
		this.options.groupsHash = {};

		this.svgdom = svgdom;

		if(this.options.places){
			for (i = 0; i < this.options.places.length; i++)this.options.placesHash[this.options.places[i].id] = i;
		}

		if(this.options.groups){
			this.options.groups.push({id:0});
			for (i = 0; i < this.options.groups.length; i++)this.options.groupsHash[this.options.groups[i].id] = i;
		}

        var self = this;

        for (i in this.options.placesHash)
            this.selected[i] = false;

        $(document).on("svg.save", function(e, data){
            self.options.saveCallback(self.save(), data);
        });

        if (self.options.clicable) {
            jQuery("#stations > g > g", this.svgdom).css({"cursor": "pointer"});
            jQuery("#stations > g > text", this.svgdom).css({"cursor": "pointer"});
        }else{
            jQuery("#stations > g > g", this.svgdom).css({"cursor": "default"});
            jQuery("#stations > g > text", this.svgdom).css({"cursor": "default"});
		}

		$("#stations > g > g", this.svgdom).unbind('click');
		$("#stations > g > text", this.svgdom).unbind('click');

        $("#stations > g > g", this.svgdom).click(function(){
            id = $(this).attr("id");
            self.onClick(id);
        });

        $("#stations > g > text", this.svgdom).click(function(){
            id = $(this).siblings("g").attr("id");
            self.onClick(id);
        });

		var me = this;
		setTimeout(function(){
            me.selectUnion(me.save());
        }, 100);
    },

    _getId: function(stationId) {
        return parseInt(stationId.replace('st', ''));
    },

    declickAll: function() {
        $.each(this.selected, function(stId, selected) {
            // console.log(stId, selected, $('iframe').contents().find('#st' + stId))
            if (selected && stId != 'null') {
                $('iframe').contents().find('#st' + stId).click()
            }
        })
    },

    deselectAll: function(passOff) {
        // ЭТО УЖЕ БЫЛО ЗАКОММЕНТИРОВАНО
        //if (!passOff) $(document).off("svg.save");
        // ЭТО ЗАКОММЕНТИРОВАЛ Я
        // for (i in this.options.placesHash)
        //     this.selected[i] = false;
        $.each(this.selected, function(index, selected) {
            if (selected) {
                this.selected[index] = false
            }
        }.bind(this));

        jQuery("#stations > g > g", this.svgdom).each(function(){
            $(this).find("path").css({"fill-opacity": opacity});
            $(this).siblings("text").css({"fill-opacity": opacity});
        });
        jQuery("#lines path", this.svgdom).css("fill-opacity", opacity);
        jQuery("#elements path", this.svgdom).css("fill-opacity", opacity);

    },

    selectAll: function() {
        $.each(this.options.placesHash, function(index, value) {
            this.selected[value] = true;
        }.bind(this))
        // for (i in this.options.placesHash)
        //     this.selected[i] = true;
        jQuery("#stations > g > g", this.svgdom).each(function(){
            $(this).find("path").css({"fill-opacity": "1"});
            $(this).siblings("text").css({"fill-opacity": "1"});
        });
        jQuery("#lines path", this.svgdom).css("fill-opacity", "1");

        for(var gId in this.options.unions){
            try{
                $(this.svgdom).find('#g'+gId)[0].children[0].style.strokeOpacity = 1;
            }catch(e){

            };
        };
    },

    toggleGroup: function(groupId) {
        if (!this.options.groups || !this.options.groupsHash) return false;
        index = this.options.groupsHash[groupId];
        console.log(this.options.groups[index].selected);
        if (!this.options.groups[index].selected) {
            if  (groupId == 0) {
                this.selectAll()
                for (i = 0; i < this.options.groups.length; i++)
                    this.options.groups[i].selected = true;
            }
            else {
				this.select(this.options.groups[index].points);
            }
            this.options.groups[index].selected = true;
        } else {
            if (groupId == 0) {
                this.deselectAll(true);
                for (i = 0; i < this.options.groups.length; i++)
                    this.options.groups[i].selected = false;
            }
            else {
                this.deselect(this.options.groups[index].points);
            }
            this.options.groups[index].selected = false;
        }

		this.selectUnion(this.save());
        scope.$apply()
		return false;
    },

    deselect: function(id, ignoreRel) {

        var self = this;
        if ((id instanceof Array) && !(id instanceof String)) {
            for (i = 0; i < id.length; i++) {
                iid = id[i];
                if (!iid) continue;
                console.log(iid);
                this.selected[iid] = false;
                if (!ignoreRel) {
                    place = this.options.places[this.options.placesHash[iid]];
                    if (place && place.rel) {
                        rel = place.rel.split(",");
                        for (j = 0; j < rel.length; j++) this.deselect(rel[j], true);
                    }
                }
                g = jQuery("#stations #st"+iid, this.svgdom);
                g.find("path").css({"fill-opacity": opacity});
                g.siblings("text").css({"fill-opacity": opacity});
            }
        } else {
			id = id.replace('st', '');
            this.selected[id] = false;
            if (!ignoreRel) {
                place = this.options.places[this.options.placesHash[id]];
                if (place && place.rel) {
                    rel = place.rel.split(",");
                    for (j = 0; j < rel.length; j++) this.deselect(rel[j], true);
                }
            }
            g = jQuery("#stations #st"+id, this.svgdom);
            g.find("path").css({"fill-opacity": opacity});

            // 1. Смотрим, все ли станции внутри union выключены перед выключением текста
            // находим нужный union
            union = _.find(this.options.unions, function(union) {
                return union.indexOf('st' + id) != -1
            });

            // если станция внутри union, смотрим, есть ли там еще выбранные станции?
            all_union_deselected = true // отвечает за то, что все станции внутри union выключены

            if (union !== undefined) {
                $.each(union, function(index, st) {
                    // если есть выбранная станция внутри union
                    if (self.selected[self._getId(st)]) {
                        all_union_deselected = false
                        return
                    }
                })
            }

            // отключаем текст только в том случае, если все станции внутри выключены
            if (all_union_deselected) {
                g.siblings("text").css({"fill-opacity": opacity});
            }
        }
        if (!ignoreRel) {
			for(var lineId in this.options.lines_stations){
				var stationIdFrom = this.options.lines_stations[lineId][0].replace('st', '');
				var stationIdTo = this.options.lines_stations[lineId][1].replace('st', '');

                if (!this.selected[stationIdFrom] || !this.selected[stationIdTo]){
                    $('#'+lineId, this.svgdom).css("fill-opacity", opacity);
				}
			}
        }
    },

	selectUnion:function(ids){
		$('#elements path', this.svgdom).css('fill-opacity', opacity);

		var unions = this.options.unions;

        for(var gId in unions){
            for(var j in unions[gId]){
				try{var union_point = unions[gId][j].replace('st', '');}catch(e){};
                if(ids && ids.indexOf(union_point+'') != -1){
                    try{$('#elements > #g'+gId+' > path', this.svgdom).css('fill-opacity', 1);}catch(e){};
                    break;
                };
            };
        };
    },

	select: function(id, ignoreRel) {
        // console.log(id)
        if ((id instanceof Array) && !(id instanceof String)) {
            if(!this.relSS){
                this.selectUnion(id);
                this.relSS = false;
            }
            for (i = 0; i < id.length; i++) {
                iid = id[i];
                if (!iid) continue;
                this.selected[iid] = true;
                if (!ignoreRel) {
                    place = this.options.places[this.options.placesHash[iid]];
                    if (place && place.rel) {
                        rel = place.rel.split(",");
                        for (j = 0; j < rel.length; j++) this.select(rel[j], true);
                    }
                }
                g = jQuery("#stations #st"+iid, this.svgdom);
                g.find("path").css({"fill-opacity": "1"});
                g.siblings("text").css({"fill-opacity": "1"});
            }
        } else {
			try{id = id.replace('st', '');}catch(e){}

            if (!ignoreRel) {
                place = this.options.places[this.options.placesHash[id]];
                if (place && place.rel) {
                    rel = place.rel.split(",");
                    for (j = 0; j < rel.length; j++) this.select(rel[j], true);
                }
            }
            g = jQuery("#stations #st"+id, this.svgdom);
            g.find("path").css({"fill-opacity": "1"});
            g.siblings("text").css({"fill-opacity": "1"});
            this.selected[id] = true;
        }
        if (!ignoreRel) {
			for(var lineId in this.options.lines_stations){
				var stationIdFrom = this.options.lines_stations[lineId][0].replace('st', '');
				var stationIdTo = this.options.lines_stations[lineId][1].replace('st', '');

                if (this.selected[stationIdFrom] && this.selected[stationIdTo]){
                    // console.log(lineId, this.options.lines_stations[lineId])
                    $('#'+lineId, this.svgdom).css("fill-opacity", "1");
				}
			}
        }
    },

    onClick: function(id) {
		id = id.replace('st', '');
        if (this.options.clicable) {
            if (this.selected[id]) this.deselect(id);
            else this.select(id);
        }
        if(typeof this.options.clickCallback == 'function'){
			// place = this.options.places[this.options.placesHash[id]];
			this.options.clickCallback(id);
		}
        this.selectUnion(this.save());
        scope.$apply()
    },
    getCount: function() {
        count = 0
        $.each(this.selected, function(index, value) {
            if (value) {
                count++
            }
        })
        return count
        // return Object.keys(this.selected).length;
    },
    getSelected: function() {
        selected = []
        $.each(this.selected, function(index, value) {
            if (value && index != 'undefined' && index != 'null') {
                selected.push(parseInt(index))
            }
        })
        return selected
        // return Object.keys(this.selected).length;
    },
    save: function() {
        saved = [];
        for (i in this.selected)
            if (this.selected[i] && i != 'undefined' && i != 'null')
                saved.push(i);
        return saved;
    }
}

// CHECK LINES SCRIPT
// lines = JSON.parse($('iframe').contents().find('rel#lines_stations').text());
//
// lp = 1
// $.each lines, (key, data) ->
//     setTimeout ->
//         console.clear()
//         console.log "Checking #{key}"
//         scope.SvgMap.map.declickAll()
//         scope.SvgMap.selected = []
//         $.each data, (index, st) ->
//             setTimeout ->
//                 el = $('iframe').contents().find('#' + st)
//                 el.click()
//             , index * 1000
//     , lp++ * 3000
