// 定义初始化信息
var width=256;
var height=200;
var innerRadius = 80;
var outerRadius = 100;
var arcMin = -Math.PI * 2 / 3;
var arcMax = Math.PI * 2 / 3;
var arc = d3.arc().innerRadius(80).outerRadius(100).startAngle(arcMin);

// 图 1
var svg = d3.select("#cg1")
var g = svg.append("g").attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
g.append("text").attr("class", "gauge-title").style("alignment-baseline", "central").style("text-anchor", "middle").attr("y", -30).text("CPU 使用率");
var valueLabel = g.append("text").attr("class", "gauge-value").style("alignment-baseline", "central").style("text-anchor", "middle").style("color", "#333333").attr("y", 5).text(0.00);        
g.append("text").attr("class", "gauge-unity").style("alignment-baseline", "central").style("text-anchor", "middle").attr("y", 30).text("%");
var background = g.append("path").datum({endAngle:arcMax}).style("fill", "rgba(255,255,255,0.3)").attr("d", arc);
var currentAngle = 0.00 * (((arcMax-arcMin) + arcMin) * 100);
var foreground = g.append("path").datum({endAngle: -2.05}).style("fill", "rgba(255,255,255,0.6)").attr("d", arc);

// 图 2
var svg2 = d3.select("#cg2")
var g2 = svg2.append("g").attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
g2.append("text").attr("class", "gauge-title").style("alignment-baseline", "central").style("text-anchor", "middle").attr("y", -30).text("内存使用率");
var valueLabel2 = g2.append("text").attr("class", "gauge-value").style("alignment-baseline", "central").style("text-anchor", "middle").style("color", "#333333").attr("y", 5).text(0.00);        
g2.append("text").attr("class", "gauge-unity").style("alignment-baseline", "central").style("text-anchor", "middle").attr("y", 30).text("%");
var background2 = g2.append("path").datum({endAngle:arcMax}).style("fill", "rgba(255,255,255,0.3)").attr("d", arc);
var currentAngle2 = 0.00 * (((arcMax-arcMin) + arcMin) * 100);
var foreground2 = g2.append("path").datum({endAngle: -2.05}).style("fill", "rgba(255,255,255,0.6)").attr("d", arc);

function updateCr(newValue) {
	valueLabel.text(newValue);
	var temp_val = 4.2 * (newValue / 100) - 2.05;
	foreground.datum({endAngle:currentAngle + temp_val});
	foreground.transition().duration(750).attr("d", arc);
}

function updateCr2(newValue) {
	valueLabel2.text(newValue);
	var temp_val = 4.2 * (newValue / 100) - 2.05;
	foreground2.datum({endAngle:currentAngle + temp_val});
	foreground2.transition().duration(750).attr("d", arc);
}

function IntervalFunc() {
	
	// CPU 负载获取
	var htmlobj = $.ajax({
		url: '?s=load',
		async: true,
		type: 'GET',
		success: function() {
			console.log(htmlobj.responseText);
			var newValue = htmlobj.responseText;
			updateCr(newValue);
		}
	});
	
	// 内存信息获取
	var htmlobj2 = $.ajax({
		url: '?s=mem',
		async: true,
		type: 'GET',
		success: function() {
			var json = JSON.parse(htmlobj2.responseText);
			var newValue = (json.used / json.total) * 100;
			newValue = newValue.toFixed(2);
			memused.innerHTML = (json.used / 1024).toFixed(2) + "GB";
			memtotal.innerHTML = (json.total / 1024).toFixed(2) + "GB";
			updateCr2(newValue);
		}
	});
	
	// 系统温度获取
	var htmlobj3 = $.ajax({
		url: '?s=temp',
		async: true,
		type: 'GET',
		success: function() {
			console.log(htmlobj3.responseText);
			var newValue = htmlobj3.responseText;
			$("#temperature").html(newValue + "°C");
		}
	});
}

window.onload = function() {
	IntervalFunc();
	setInterval("IntervalFunc()", 5000);
	setTimeout(function() {
		$("#cg1").fadeIn(500);
	}, 1000);
	setTimeout(function() {
		$("#cg2").fadeIn(500);
	}, 1500);
}