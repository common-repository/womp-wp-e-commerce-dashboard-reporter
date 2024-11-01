google.load("visualization", "1", {packages:["corechart","table"]});
google.setOnLoadCallback(google_loaded);

var womp_wpsc_dr = {};

function google_loaded() {

	womp_wpsc_dr.gvStuff = function(div,gTypes) {
		this.gData = {};
		this.gTableProperty = {};
		this.gGraphProperty = {};
		this.gTypes = typeof(gTypes) != 'undefined' ? gTypes : ['table'];
		this.div = div;
		this.pfx = '#' + div + '-';
		this.tabFooter = [];

		this.q = function(n) { return jQuery(this.pfx+n); }

		this.do_ajax = function() {

			this.q("loading").show();
			this.q("switch").hide();
			this.q("reload").hide();

			var s = {};
			s.that=this;
			s.type = "POST";
			s.url = ajaxurl;
			s.data = this.q("form").serializeArray();

			s.success = function(r) {
				this.a=this.that;
				try{
					eval("var d="+r);
					if (d == -1) {
						jQuery('#'+this.a.div).html("Your session has expired. Please login again.")
					} else if (d.status=='ok') {
						this.a.gData = new google.visualization.DataTable(d.table);
						this.a.tabFooter = d.tabFooter;
						this.a.gTableProperty = d.TableProperty;
						this.a.gGraphProperty = d.GraphProperty;
						this.a.draw();
					} else {
						jQuery('#'+this.a.div).html(d.message);
						this.a.gData = {};
						this.a.q("switch").hide();
					}
				} catch(e) {
					jQuery('#'+this.a.div).html('An error has occurred: '+e.message);
				}
				this.a.q("loading").hide();
				this.a.q("reload").show();
			} //End success

			s.error = function(r) {
				this.q('#'+this.a.div).html("Error!");
				this.q("loading").hide();
				this.q("reload").show();
			}

			jQuery.ajax(s);
		}
		this.gSwitch = function() {
			var e=this.q('gType');
			var v=e.val();
			for (i=0;i<this.gTypes.length;i++) {
				if (v==this.gTypes[i]) break;
			}
			if (i >= this.gTypes.length - 1)
				e.val(this.gTypes[0]);
			else
				e.val(this.gTypes[i+1]);

			this.draw();
		}
		this.addFooter = function(){
			if (!this.tabFooter.length) return;
			if (document.getElementById(this.div+'-table-footer')) return;
			var tables=document.getElementById(this.div).getElementsByTagName('TABLE');
			if (!tables) return;

			for (i=0; i<tables.length; i++) {
				if (tables[i].className!='google-visualization-table-table') continue;

				var r=tables[i].insertRow(tables[i].rows.length);
				r.id=this.div+'-table-footer'
				var c;
				r.className='google-visualization-table-tr-head';
				for (j=0; j<this.tabFooter.length; j++){
					c=r.insertCell(j);
					c.className='google-visualization-table-th'
					c.innerHTML=this.tabFooter[j][0]
					c.colSpan=this.tabFooter[j][1]
				}
			}
		}
		this.draw = function() {
			var that = this;
			var v = this.q("gType").val();
			if (this.gTypes.length==1) {
				v=this.gTypes[0];
			} else {
				this.q('switch').show();
				for (i=0;i<this.gTypes.length;i++) {
					if (v==this.gTypes[i]) break;
				}
				if (i >= this.gTypes.length)
					v = this.gTypes[0];
			}
			if (v=='table') {
				var vis = new google.visualization.Table(document.getElementById(this.div));
				google.visualization.events.addListener(vis, 'ready', function(){that.addFooter()});
				google.visualization.events.addListener(vis, 'sort', function(){that.addFooter()});
				google.visualization.events.addListener(vis, 'page', function(){that.addFooter()});
				vis.draw(this.gData, this.gTableProperty);
			} else if (v=='PieChart') {
				var vis = new google.visualization.PieChart(document.getElementById(this.div));
				vis.draw(this.gData, this.gGraphProperty);
			} else if (v=='ColumnChart') {
				var vis = new google.visualization.ColumnChart(document.getElementById(this.div));
				vis.draw(this.gData, this.gGraphProperty);
			} else {

			}
			
		}

		if (typeof(this.div)=='undefined')
			return;

		this.q("loading").show();
		this.q("switch").hide();
		this.q("reload").hide();
		this.do_ajax();

	}

	womp_wpsc_dr.ProductSales = function() {

		this.q('custom-submit').hide();

		var that=this;

		this.q("period").change(function() {
			if (that.q('period').val() == 'custom') {
				that.q("start-date").show();
				that.q("end-date").show();
				that.q("custom-submit").show();
				that.q("reload").hide();
			} else {
				that.q("start-date").hide();
				that.q("end-date").hide();
				that.q("custom-submit").hide();
				that.q("reload").show();
				that.do_ajax();
			}
		});

		this.q("custom-submit").click(function() { that.do_ajax(); });

		this.q("start-date").datepicker({dateFormat: 'dd-mm-yy'});
		this.q("end-date").datepicker({dateFormat: 'dd-mm-yy'});

	}

	womp_wpsc_dr.SalesGraph = function() {
		var that=this;
		this.q("details").change(function() { that.do_ajax(); });
		this.q("values").change(function() { that.do_ajax(); });
		this.q("interval").change(function() { that.do_ajax(); });
	}

	
	womp_wpsc_dr.ProductSales.prototype = new womp_wpsc_dr.gvStuff('womp-wpcs-dr-product-sales', ['table', 'PieChart']);
	womp_wpsc_dr.product_sales = new womp_wpsc_dr.ProductSales();

	womp_wpsc_dr.SalesGraph.prototype = new womp_wpsc_dr.gvStuff('womp-wpcs-dr-sales', ['ColumnChart']);
	womp_wpsc_dr.sales = new womp_wpsc_dr.SalesGraph();

	womp_wpsc_dr.recent_orders = new womp_wpsc_dr.gvStuff('womp-wpcs-dr-recent-orders');

} // End of google_loaded()

