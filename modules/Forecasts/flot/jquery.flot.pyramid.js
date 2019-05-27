/*
 * The MIT License
Copyright (c) 2012 by Juergen Marsch

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

/*
Flot plugin for Pyramid data sets

series: {
    pyramid: null or true
}

data: [
  jQuery.plot(jQuery("#placeholder"), [{ data: [ ... ], pyramid: true }])

*/

(function ($) {
    var options = {
		series: { 
			pyramid: {
				active: false
				, show: false
				, mode: "pyramid"
				, fill: true
				, label: {
					show: false 
					,align:"center"
					,font: "20px Times New Roman"
					,fillStyle: "Black"
				}
			}
		}
	};

    function init(plot) {
		var  data = null, opt = null, canvas = null, target = null,hl = null,dataHeight = null, dataMax,centerX;

		plot.hooks.processOptions.push(processOptions);

		function processOptions(plot,options){
		  options.grid.show = false;
			opt = options;
			if (options.series.pyramid.active){	
				plot.hooks.draw.push(draw);
				plot.hooks.bindEvents.push(bindEvents);
				plot.hooks.drawOverlay.push(drawOverlay);
			}
		}

		function draw(plot, ctx){
			var series;
			canvas = plot.getCanvas();
			target = jQuery(canvas).parent();
			data = plot.getData(); 
			for (var i = 0; i < data.length; i++){
				series = data[i];
				if (series.pyramid.show) {
					dataMax = series.data[series.data.length-1].value;
					dataHeight = canvas.height / series.data.length;
					for (var j = (series.data.length-1); j > 0; j--) {
						var lowWidth,highWidth,lowY;
						lowWidth = series.data[j].value * canvas.width / dataMax;
						lowY = canvas.height - (dataHeight * j);
						if((j+1)==series.data.length) highWidth = lowWidth; else highWidth = series.data[j+1].value * canvas.width / dataMax;
						if (jQuery.isFunction(series.pyramid.mode)) {
							series.pyramid.mode(ctx,canvas,series, lowY, lowWidth, dataHeight, highWidth, opt.colors[j], false);
						}
						else {
							switch (series.pyramid.mode) {
								case "pyramid":
									drawPyramid(ctx, series, lowY, lowWidth, dataHeight, highWidth, opt.colors[j], false);
									break;
								case "slice":
									drawSlice(ctx, series, lowY, lowWidth, dataHeight, highWidth, opt.colors[j], false);
									break;
								default:
									drawPyramid(ctx, series, lowY, lowWidth, dataHeight, highWidth, opt.colors[j], false);
							}
						}
						if(series.pyramid.label.show==true){ drawLabel(ctx,series,series.data[j],lowY - dataHeight / 2);}
					}
				}
			}
		}

		function drawLabel(ctx,series,data,posY){
			var posX = 0;
			var posY = 0;
			ctx.font = series.pyramid.label.font;
			ctx.fillStyle = series.pyramid.label.fillStyle;
			var metrics = ctx.measureText(data.label);
			switch(series.pyramid.label.align) {
				case "center":
					posX = canvas.width / 2 - metrics.width / 2;
					break;
				case "left":
					posX = 0;
					break;
				case "right":
					posX = canvas.width - metrics.width;
					break;	 								
				default:
					posX = canvas.width - metrics.width;
			}
	    	ctx.fillText(data.label, posX, posY);
		}

		function drawPyramid(ctx,series,lowY,lowWidth,dataHeight,highWidth,c,overlay){
			var centerX = canvas.width / 2;
			ctx.beginPath();
			ctx.lineWidth = 1;
			ctx.fillStyle = c;
			ctx.strokeStyle = c;
			ctx.moveTo(centerX - lowWidth / 2,lowY);
			ctx.lineTo(centerX + lowWidth / 2,lowY);
			ctx.lineTo(centerX + highWidth / 2,lowY - dataHeight);
			ctx.lineTo(centerX - highWidth / 2,lowY - dataHeight);
			ctx.closePath();
			ctx.fill();
		}

		function drawSlice(ctx,series,lowY,lowWidth,dataHeight,highWidth,c,overlay){
			var centerX = canvas.width / 2;
			var centerY = lowY - dataHeight/2;
			ctx.save();
			ctx.beginPath();
			ctx.lineWidth = 1;
			ctx.fillStyle = c;
			ctx.strokeStyle = c;
			ctx.translate(centerX - lowWidth / 2,centerY - dataHeight/2);
			ctx.scale(lowWidth / 2,dataHeight/2);
			ctx.arc(1,1,1,0,2 * Math.PI,false);
			ctx.closePath();
			ctx.fill();
			ctx.restore();
		}

		function bindEvents(plot, eventHolder){
			var options = plot.getOptions();
			hl = new HighLighting(plot, eventHolder, findNearby, options.series.pyramid.active);
		}

		function findNearby(mousex, mousey){
			var r;
			data = plot.getData();
			r = new NearByReturn();
			r.item = findNearByItem(mousex,mousey);
			r.edit = new NearByReturnData();
			return r;

			function findNearByItem(){
				var centerX = canvas.width / 2;
				var serie,r = new NearByReturnData();
				for(var i = 0;i < data.length;i++){
					serie = data[i];
					if (serie.pyramid.show) {
						var ln = Math.floor((canvas.height - mousey) / dataHeight);
						if(ln>=0 && ln<serie.data.length){
							var w = serie.data[ln].value * canvas.width / dataMax;
							if (mousex > (centerX - w / 2) && mousex < (centerX + w / 2)) {
								r.found = true;
								r.serie = i;
								r.datapoint = ln;
								r.value = serie.data[ln].value;
								r.label = serie.data[ln].label;
							}
						}
					}
				}
				return r;
			}
		}

		function drawOverlay(plot, octx){
			octx.save();
			octx.clearRect(0, 0, target.width(), target.height());
			for (var i = 0; i < hl.highlights.length; ++i) { drawHighlight(hl.highlights[i]);}
			octx.restore();

			function drawHighlight(s){
				//var c = "rgba(255, 255, 255, " + s.series.pyramid.highlight.opacity + ")";
				//drawPyramid(octx, s.series, s.point, c, true);
			}
		}
	}

	jQuery.plot.plugins.push({
		init: init,
		options: options,
		name: 'pyramid',
		version: '0.1'
    });

})(jQuery);
