var mUrllib = require('urllib');
var mUrl = require('url');
var mCheerio = require('cheerio');
var mFs = require('fs');
var mChildProcess = require('child_process')
var TaskQueue = require("./taskqueue");


function isDeal(listPrice, currentPrice) {
	return currentPrice / listPrice < 0.4;
}

var gParsers = {

	parseCates: function(_this, task, $) {
		var cates = $('.primary >li');
		cates.map(function(i, li) {
			li = $(li);
			var a = $(li).children('a');
			if(a.length == 0) return;
			var cateName = a.text().toLowerCase().trim();
			if(cateName.length == 0 || cateName != "clearance") return;

			li.find('>ul li >a').map(function(i, a) {
				a = $(a);
				if(a.next('ul').length) return;

				var cateId = a.parent().attr('id').substr(4);
				cateId = parseInt(cateId.substr(0, cateId.length - 4));

				var url = task.meta.API + '&categoryId=' + cateId + '&start=0&quantity=500';
				var nextTask = {
					meta: task.meta,
					type: 1,
					url: url,
					cateUrl: mUrl.resolve(task.url, a.attr('href')),
					cateId: cateId,
					cateName: a.text().trim(),
					page: 1,
					dataType: 'json'
				};
				taskQueue.add(nextTask);
				//console.log("AddCate->", task.meta.company, nextTask.cateName);
			});

		});
	},

	parseProducts: function(_this, task, js) {
		var prods = [];
		js.categories[0].products.map(function(prod) {
			var listPrice = prod.price.highListPrice.substr(1);
			var currentPrice = prod.price.priceLow.substr(1);
			if(!isDeal(parseFloat(listPrice), parseFloat(currentPrice))) return;
			prods.push([
				prod.id,
				task.meta.buildImgUrl(task.meta, prod),
				mUrl.resolve(task.cateUrl, prod.productUrl),
				prod.name,
				listPrice,
				currentPrice
			]);
		});
		if(prods.length <= 0) return;

		gProducts.push([
			task.meta.topCateId,
			0, //task.cateId
			task.cateName,
			prods
		]);

		console.log(
			task.meta.company,
			"-> Page: ", task.page,
			", Cate: ", task.cateName,
			", Prods: ", prods.length
		);

		if(task.page >= 10) {
			console.log(">>Error: Maximun Page Reached");
			return;
		}

		var start = task.page * 500;
		var url = task.meta.API + '&categoryId=' + task.cateId + '&start=' + start + '&quantity=500';
		taskQueue.add(Object.assign({}, task, {url: url, page: task.page + 1}));
	}

};


function executer(task) {
	var _this = this;

	mUrllib.request(task.url, {gzip: true})
	.then(function(result) {

		return new Promise(function(resolve, reject) {
			try {
				var data = result.data.toString();
				if(task.dataType == 'html')
					resolve(mCheerio.load(data));
				else if(task.dataType == 'json')
					resolve(JSON.parse(data));
				else
					resolve(data);

			} catch (err) {
				reject(err);
			}
		});
		
	}).then(function(data) {
		var company = task.meta.company;
		if(task.type == 0) {
			if(typeof gParsers.parseCates == 'function')
				gParsers.parseCates(_this, task, data);
			else {
				var parseCates = gParsers.parseCates[company] || gParsers.parseCates.default;
				parseCates(_this, task, data);
			}

		} else if(task.type == 1) {
			if(typeof gParsers.parseProducts == 'function')
				gParsers.parseProducts(_this, task, data);
			else {
				var parseProducts = gParsers.parseProducts[company] || gParsers.parseProducts.default;
				parseProducts(_this, task, data);
			}
		}

	}).catch(function(err) {
		console.log(">>Error", err);

	}).then(function() {

		_this.done();

	});
}


function abercrombie_buildImgUrl(meta, prod) {
	var ps = prod.imagePresets;
	return `//anf.scene7.com/is/image/anf/anf_${prod.productCollection}_${prod.urlSeq}_${prod.imageNameSuffix}?$${ps.imagePresetPrefix}${ps.imagePresetSuffix}$`;
}

function hollisterco_buildImgUrl(meta, prod) {
	var ps = prod.imagePresets;
	return `//anf.scene7.com/is/image/anf/hol_${prod.productCollection}_${prod.urlSeq}_${prod.imageNameSuffix}?$${ps.imagePresetPrefix}${ps.imagePresetSuffix}`;
}

var tasks = [
	{
		type: 0,
		url: 'https://www.abercrombie.com/shop/us/mens-clearance',
		meta: {
			buildImgUrl: abercrombie_buildImgUrl,
			company: 'abercrombie',
			topCateId: 12202,
			API: 'https://www.abercrombie.com/webapp/wcs/stores/servlet/AjaxNavResults?storeId=10051&catalogId=10901&langId=-1&ofp=true'
		},
		dataType: 'html'
	},
	
	{
		type: 0,
		url: 'https://www.abercrombie.com/shop/us/womens-clearance',
		meta: {
			buildImgUrl: abercrombie_buildImgUrl,
			company: 'abercrombie',
			topCateId: 12203,
			API: 'https://www.abercrombie.com/webapp/wcs/stores/servlet/AjaxNavResults?storeId=10051&catalogId=10901&langId=-1&ofp=true'
		},
		dataType: 'html'
	},
	{
		type: 0,
		url: 'https://www.hollisterco.com/shop/us/guys-clearance',
		meta: {
			buildImgUrl: hollisterco_buildImgUrl,
			company: 'hollisterco',
			topCateId: 12551,
			API: 'https://www.hollisterco.com/webapp/wcs/stores/servlet/AjaxNavResults?storeId=10251&catalogId=10201&langId=-1&ofp=true'
		},
		dataType: 'html'
	},
	{
		type: 0,
		url: 'https://www.hollisterco.com/shop/us/girls-clearance',
		meta: {
			buildImgUrl: hollisterco_buildImgUrl,
			company: 'hollisterco',
			topCateId: 12552,
			API: 'https://www.hollisterco.com/webapp/wcs/stores/servlet/AjaxNavResults?storeId=10251&catalogId=10201&langId=-1&ofp=true'
		},
		dataType: 'html'
	},
];

var taskQueue = TaskQueue(10, executer, 2).on('finish',  function() {
	mFs.writeFileSync('data_tmp.txt', JSON.stringify(gProducts));
	console.log("Loading into Database..");
	console.log(mChildProcess.execSync("/usr/bin/php-cli af.php 2>&1", {encoding: 'utf8'}));
	console.log("Done");

});

var gProducts = [];

for(var task of tasks)
	taskQueue.add(task);

