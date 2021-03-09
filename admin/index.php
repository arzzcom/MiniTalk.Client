<?php
/**
 * 이 파일은 미니톡 클라이언트의 일부입니다. (https://www.minitalk.io)
 *
 * 미니톡 클라이언트 관리자 레이아웃을 출력한다.
 * 관리자페이지와 관련된 파일은 ExtJS 라이센스정책에 따라 GPLv3 라이센스로 배포됩니다.
 * 
 * @file /admin/index.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 7.0.0
 * @modified 2021. 1. 24.
 */
REQUIRE '../configs/init.config.php';
if ($_CONFIGS->installed === false) {
	header("location:../install");
	exit;
}

$MINITALK = new Minitalk();
$logged = $MINITALK->getAdminLogged();

if ($logged !== null && $logged->language == 'ko') {
	$fontStyle = '../styles/font.css.php?font=moimz,XEIcon,FontAwesome,NanumBarunGothic,OpenSans&default=NanumBarunGothic';
} else {
	$fontStyle = '../styles/font.css.php?font=moimz,XEIcon,FontAwesome,OpenSans&default=OpenSans';
}

$current = Request('menu') ? Request('menu') : 'server';
$hasServer = is_dir(__MINITALK_PATH__.'/server') == true;
?>
<!DOCTYPE HTML>
<html lang="<?php echo $logged == null ? 'en' : $logged->language; ?>">
<head>
<meta charset="utf-8">
<title>Minitalk Administrator</title>
<script src="../scripts/jquery.js?t=<?php echo filemtime('../scripts/jquery.js'); ?>"></script>
<script src="../scripts/jquery.extend.js?t=<?php echo filemtime('../scripts/jquery.extend.js'); ?>"></script>
<script src="../scripts/moment.js?t=<?php echo filemtime('../scripts/moment.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo $fontStyle; ?>" type="text/css">
<?php if ($logged !== null) { ?>
<link rel="stylesheet" href="./styles/style.css?t=<?php echo filemtime('./styles/style.css'); ?>" type="text/css">
<link rel="stylesheet" href="../styles/extjs.css?t=<?php echo filemtime('../styles/extjs.css'); ?>" type="text/css">
<link rel="stylesheet" href="../styles/extjs.extend.css?t=<?php echo filemtime('../styles/extjs.extend.css'); ?>" type="text/css">
<script src="../scripts/extjs.js?t=<?php echo filemtime('../scripts/extjs.js'); ?>"></script>
<script src="../scripts/extjs.extend.js?t=<?php echo filemtime('../scripts/extjs.extend.js'); ?>"></script>
<?php } else { ?>
<link rel="stylesheet" href="./styles/login.css?t=<?php echo filemtime('./styles/login.css'); ?>" type="text/css">
<?php } ?>
<script src="./scripts/script.js?t=<?php echo filemtime('./scripts/script.js'); ?>"></script>
<script src="../scripts/language.js.php?language=<?php echo $logged == null ? 'en' : $logged->language; ?>"></script>
<link rel="shortcut icon" type="image/x-icon" href="//www.moimz.com/modules/moimz/images/Minitalk.ico">
</head>
<body<?php echo $logged === null ? ' class="login"' : ''; ?>>
<?php
if ($logged === null) {
	INCLUDE './login.php';
} else {
	$menuIcons = array('server'=>'xi-cloud-network','channel'=>'xi-chat','history'=>'xi-time-back','attachment'=>'xi-archive','banip'=>'xi-slash-circle','broadcast'=>'xi-signal','admin'=>'xi-crown');
?>
<header id="MinitalkHeader">
	<h1>Minitalk <small>Administrator</small></h1>
	
	<ul data-role="menu">
		<li data-role="more">
			<button type="button"><i class="mi mi-angle-down"></i></button>
			<ul></ul>
		</li>
		<?php foreach ($MINITALK->getText('admin/menu') as $menu=>$title) { if (in_array($menu,array('log','broadcast')) == true && $hasServer == false) continue; ?>
		<li<?php echo $menu == 'server' ? ' class="selected"' : ''; ?>><button data-tab="<?php echo $menu; ?>"><i class="xi <?php echo $menuIcons[$menu]; ?>"></i><?php echo $title; ?></button></li>
		<?php } ?>
	</ul>
	
	<aside>
		<button type="button" onclick="Admin.logout();">LOGOUT</button>
	</aside>
</header>

<footer id="MinitalkFooter">
	Copyright (c) <?php echo date('Y'); ?> Minitalk <?php echo __MINITALK_VERSION__; ?>, MIT License / <?php echo $_SERVER['SERVER_ADDR']; ?>
</footer>

<script>
Ext.onReady(function () {
	new Ext.Viewport({
		id:"AdminViewport",
		layout:{type:"border"},
		items:[
			new Ext.Panel({
				region:"north",
				height:52,
				border:false,
				contentEl:"MinitalkHeader",
				cls:"x-visible-panel"
			}),
			new Ext.TabPanel({
				id:"MinitalkTabPanel",
				border:false,
				region:"center",
				activeTab:0,
				items:[
					new Ext.grid.Panel({
						id:"MinitalkPanel-server",
						hasServer:<?php echo $hasServer == true ? 'true' : 'false'; ?>,
						tbar:[
							new Ext.Button({
								text:Admin.getText("server/add"),
								iconCls:"mi mi-plus",
								handler:function() {
									Admin.server.add();
								}
							}),
							new Ext.Button({
								text:Admin.getText("server/delete"),
								iconCls:"mi mi-trash",
								handler:function() {
									Admin.server.status("delete");
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getServers"),
								reader:{type:"json"}
							},
							remoteSort:false,
							sorters:[{property:"domain",direction:"ASC"}],
							autoLoad:true,
							pageSize:0,
							groupField:"type",
							groupDir:"ASC",
							fields:["domain","status","status_message",{"name":"channel","type":"int"},{"name":"user","type":"int"},{"name":"maxuser","type":"int"},{"name":"latest_update","type":"int"},{"name":"exp_date","type":"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						columns:[{
							text:Admin.getText("server/columns/domain"),
							summaryType:"count",
							dataIndex:"domain",
							minWidth:200,
							flex:1,
							sortable:true,
							summaryRenderer:function(value) {
								return value+" server"+(value > 1 ? "s" : "");
							}
						},{
							text:Admin.getText("server/columns/status"),
							dataIndex:"status",
							width:80,
							sortable:true,
							align:"center",
							renderer:function(value,p) {
								if (value == "ONLINE") p.style = "color:blue;";
								else p.style = "color:red;";
								
								return Admin.getText("server/status/"+value);
							}
						},{
							text:Admin.getText("server/columns/status_message"),
							dataIndex:"status_message",
							width:250,
							sortable:true,
							renderer:function(value,p,record) {
								if (record.data.type == "SERVER") {
									if (record.data.status == "ONLINE") {
										return "Uptime : " + moment(moment().unix() * 1000 - value.uptime * 1000).locale("ko").fromNow(true) + " / Memory : " + Minitalk.getFileSize(value.memory.rss + value.memory.heapTotal + value.memory.heapUsed + value.memory.external);
									} else {
										return;
									}
								} else {
									return value;
								}
							}
						},{
							text:Admin.getText("server/columns/channel"),
							dataIndex:"channel",
							width:120,
							sortable:true,
							align:"right",
							summaryType:"sum",
							renderer:function(value) {
								return Ext.util.Format.number(value,"0,000");
							},
							summaryRenderer:function(value) {
								return Ext.util.Format.number(value,"0,000");
							}
						},{
							text:Admin.getText("server/columns/user"),
							dataIndex:"user",
							width:120,
							sortable:true,
							align:"right",
							summaryType:"sum",
							renderer:function(value) {
								return Ext.util.Format.number(value,"0,000");
							},
							summaryRenderer:function(value) {
								return Ext.util.Format.number(value,"0,000");
							}
						},{
							text:Admin.getText("server/columns/max_user"),
							dataIndex:"max_user",
							width:120,
							sortable:true,
							align:"right",
							summaryType:"sum",
							renderer:function(value) {
								if (value == 0) return '<div style="text-align:center;">'+Admin.getText("server/unlimited")+'</div>';
								return Ext.util.Format.number(value,"0,000");
							},
							summaryRenderer:function(value) {
								if (value == 0) return '<div style="text-align:center;">'+Admin.getText("server/unlimited")+'</div>';
								return Ext.util.Format.number(value,"0,000");
							}
						},{
							text:Admin.getText("server/columns/latest_update"),
							dataIndex:"latest_update",
							width:160,
							sortable:true,
							align:"center",
							summaryType:"max",
							renderer:function(value) {
								if (value == 0) return Admin.getText("server/unknown");
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							},
							summaryRenderer:function(value) {
								if (value == 0) return Admin.getText("server/unknown");
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							}
						},{
							text:Admin.getText("server/columns/exp_date"),
							dataIndex:"exp_date",
							width:160,
							sortable:true,
							align:"center",
							summaryType:"max",
							renderer:function(value) {
								if (value == -1) return Admin.getText("server/unknown");
								else if (value == 0) return Admin.getText("server/unlimited");
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							},
							summaryRenderer:function(value) {
								if (value == 0) return Admin.getText("server/unlimited");
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							}
						}],
						selModel:new Ext.selection.CheckboxModel(),
						features:[{
							ftype:"groupingsummary",
							groupHeaderTpl:'<tpl if="name == \'SERVER\'">'+Admin.getText("server/type/SERVER")+'<tpl elseif="name == \'SERVICE\'">'+Admin.getText("server/type/SERVICE")+'</tpl>',
							hideGroupedHeader:false,
							enableGroupingMenu:false
						}],
						bbar:[
							new Ext.Button({
								iconCls:"x-tbar-loading",
								handler:function() {
									Ext.getCmp("MinitalkPanel-server").getStore().reload();
								}
							}),
							"->",
							{xtype:"tbtext",text:Admin.getText("server/grid_help")}
						],
						listeners:{
							itemdblclick:function(grid,record) {
								Admin.server.add(record.data.domain);
							},
							itemcontextmenu:function(grid,record,item,index,e) {
								var menu = new Ext.menu.Menu();
								
								menu.addTitle(record.data.domain);
								
								menu.add({
									text:Admin.getText("server/modify"),
									iconCls:"xi xi-form",
									handler:function() {
										Admin.server.add(record.data.domain);
									}
								});
								
								menu.add({
									text:Admin.getText("server/delete"),
									iconCls:"mi mi-trash",
									handler:function() {
										Admin.server.status("delete");
									}
								});
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
					new Ext.grid.Panel({
						id:"MinitalkPanel-channel",
						tbar:[
							new Ext.form.ComboBox({
								id:"MinitalkChannelCategory1",
								store:new Ext.data.JsonStore({
									proxy:{
										type:"ajax",
										url:Minitalk.getProcessUrl("@getCategories"),
										extraParams:{parent:0,is_all:"true"},
										reader:{type:"json"}
									},
									autoLoad:true,
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									fields:["idx","category",{name:"sort",type:"int"}]
								}),
								width:120,
								editable:false,
								matchFieldWidth:false,
								listConfig:{
									minWidth:120
								},
								displayField:"category",
								valueField:"idx",
								value:"",
								listeners:{
									change:function(form,value) {
										Ext.getCmp("MinitalkChannelCategory2").setValue("");
										Ext.getCmp("MinitalkChannelCategory2").getStore().getProxy().setExtraParam("parent",value ? value : -1);
										Ext.getCmp("MinitalkChannelCategory2").getStore().reload();
										
										Ext.getCmp("MinitalkPanel-channel").getStore().getProxy().setExtraParam("category1",value);
										Ext.getCmp("MinitalkPanel-channel").getStore().getProxy().setExtraParam("category2","");
										Ext.getCmp("MinitalkPanel-channel").getStore().loadPage(1);
									}
								}
							}),
							new Ext.form.ComboBox({
								id:"MinitalkChannelCategory2",
								store:new Ext.data.JsonStore({
									proxy:{
										type:"ajax",
										url:Minitalk.getProcessUrl("@getCategories"),
										extraParams:{parent:-1,is_all:"true"},
										reader:{type:"json"}
									},
									autoLoad:true,
									remoteSort:false,
									sorters:[{property:"sort",direction:"ASC"}],
									fields:["idx","category",{name:"sort",type:"int"}]
								}),
								width:120,
								editable:false,
								matchFieldWidth:false,
								listConfig:{
									minWidth:120
								},
								displayField:"category",
								valueField:"idx",
								value:"",
								listeners:{
									change:function(form,value) {
										Ext.getCmp("MinitalkPanel-channel").getStore().getProxy().setExtraParam("category2",value);
										Ext.getCmp("MinitalkPanel-channel").getStore().loadPage(1);
									}
								}
							}),
							new Ext.Button({
								iconCls:"mi mi-config",
								handler:function() {
									Admin.channel.category.panel();
								}
							}),
							"-",
							new Ext.form.TextField({
								id:"MinitalkChannelKeyword",
								width:150,
								emptyText:Admin.getText("channel/columns/channel") + " / " + Admin.getText("channel/columns/title"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkChannelSearchButton").handler();
										}
									}
								}
							}),
							new Ext.Button({
								id:"MinitalkChannelSearchButton",
								iconCls:"mi mi-search",
								handler:function() {
									Ext.getCmp("MinitalkPanel-channel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MinitalkChannelKeyword").getValue());
									Ext.getCmp("MinitalkPanel-channel").getStore().loadPage(1);
								}
							}),
							"-",
							new Ext.Button({
								text:Admin.getText("channel/add"),
								iconCls:"mi mi-plus",
								handler:function() {
									Admin.channel.add();
								}
							}),
							new Ext.Button({
								iconCls:"mi mi-trash",
								text:Admin.getText("channel/delete"),
								handler:function() {
									Admin.channel.delete();
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getChannels"),
								reader:{type:"json"}
							},
							remoteSort:true,
							sorters:[{property:"channel",direction:"ASC"}],
							autoLoad:true,
							pageSize:50,
							fields:["channel","title"],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						columns:[{
							text:Admin.getText("channel/columns/channel"),
							summaryType:"count",
							dataIndex:"channel",
							width:180,
							sortable:true
						},{
							text:Admin.getText("channel/columns/category1"),
							dataIndex:"category1",
							width:120
						},{
							text:Admin.getText("channel/columns/category2"),
							dataIndex:"category2",
							width:120
						},{
							text:Admin.getText("channel/columns/title"),
							dataIndex:"title",
							minWidth:150,
							flex:1,
							sortable:true
						},{
							text:Admin.getText("channel/columns/password"),
							dataIndex:"password",
							width:120
						},{
							text:Admin.getText("channel/columns/server"),
							dataIndex:"server",
							width:240,
							renderer:function(value,p) {
								if (value == null) {
									p.tdStyle = "color:#666;";
									return Admin.getText("channel/unknown");
								} else {
									return value.domain;
								}
							}
						},{
							text:Admin.getText("channel/columns/send_limit"),
							dataIndex:"send_limit",
							width:140,
							align:"center",
							renderer:function(value) {
								return Minitalk.getText("level/"+value);
							}
						},{
							text:Admin.getText("channel/columns/user"),
							dataIndex:"user",
							width:120,
							align:"right",
							sortable:true,
							renderer:function(value,p,record) {
								var sHTML = "";
								sHTML+= '<b style="color:blue;">'+Ext.util.Format.number(value,"0,000")+'</b> / ';
								sHTML+= Ext.util.Format.number(record.data.max_user,"0,000");
								return sHTML;
							}
						},{
							text:Admin.getText("channel/columns/options"),
							dataIndex:"options",
							width:300,
							renderer:function(value,p,record) {
								var sHTML = "";
								if (record.data.allow_nickname_edit == true) {
									sHTML+= '<i class="xi xi-check-boxout"></i>';
								} else {
									sHTML+= '<i class="xi xi-layout-full"></i>';
								}
								sHTML+= ' '+Admin.getText("channel/form/allow_nickname_edit");
								
								sHTML+= "&nbsp;&nbsp;&nbsp;";
								if (record.data.use_user_tab == true) {
									sHTML+= '<i class="xi xi-check-boxout"></i>';
								} else {
									sHTML+= '<i class="xi xi-layout-full"></i>';
								}
								sHTML+= ' '+Admin.getText("channel/form/use_user_tab");
								sHTML+= '(' + record.data.user_limit + '<i class="fa fa-caret-up"></i>)';
								
								sHTML+= "&nbsp;&nbsp;&nbsp;";
								if (record.data.use_box_tab == true) {
									sHTML+= '<i class="xi xi-check-boxout"></i>';
								} else {
									sHTML+= '<i class="xi xi-layout-full"></i>';
								}
								sHTML+= ' '+Admin.getText("channel/form/use_box_tab");
								sHTML+= '(' + record.data.box_limit + '<i class="fa fa-caret-up"></i>)';
								
								return sHTML;
							}
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:new Ext.PagingToolbar({
							store:null,
							displayInfo:false,
							items:[
								"->",
								{xtype:"tbtext",text:Admin.getText("channel/grid_help")}
							],
							listeners:{
								beforerender:function(tool) {
									tool.bindStore(tool.ownerCt.getStore());
								}
							}
						}),
						listeners:{
							itemdblclick:function(grid,record) {
								Admin.channel.add(record.data.channel);
							},
							itemcontextmenu:function(grid,record,row,index,e) {
								var menu = new Ext.menu.Menu();
								
								menu.addTitle(record.data.title);
								
								menu.add({
									text:Admin.getText("channel/preview"),
									iconCls:"xi xi-monitor",
									handler:function() {
										Admin.channel.preview(record.data);
									}
								});
								
								menu.add({
									text:Admin.getText("channel/code"),
									iconCls:"xi xi-code",
									handler:function() {
										Admin.channel.code(record.data);
									}
								});
								
								menu.add("-");
								
								menu.add({
									text:Admin.getText("channel/modify"),
									iconCls:"xi xi-form",
									handler:function() {
										Admin.channel.add(record.data.channel);
									}
								});
								
								menu.add({
									text:Admin.getText("channel/delete"),
									iconCls:"mi mi-trash",
									handler:function() {
										Admin.channel.delete();
									}
								});
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
					new Ext.grid.Panel({
						id:"MinitalkPanel-attachment",
						tbar:[
							new Ext.form.TextField({
								id:"MinitalkAttachmentKeyword",
								width:150,
								emptyText:Admin.getText("attachment/columns/name") + " / " + Admin.getText("attachment/columns/nickname"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkAttachmentButton").handler();
										}
									}
								}
							}),
							new Ext.Button({
								id:"MinitalkAttachmentButton",
								iconCls:"mi mi-search",
								handler:function() {
									Ext.getCmp("MinitalkPanel-attachment").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MinitalkAttachmentKeyword").getValue());
									Ext.getCmp("MinitalkPanel-attachment").getStore().loadPage(1);
								}
							}),
							"-",
							new Ext.Button({
								iconCls:"mi mi-trash",
								text:Admin.getText("attachment/delete_selected"),
								handler:function() {
									Admin.attachment.delete();
								}
							}),
							"->",
							new Ext.Button({
								iconCls:"mi mi-calendar",
								text:Admin.getText("attachment/delete_expired"),
								handler:function() {
									Admin.attachment.expired();
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getAttachments"),
								reader:{type:"json"}
							},
							remoteSort:true,
							sorters:[{property:"reg_date",direction:"DESC"}],
							autoLoad:true,
							pageSize:50,
							fields:["hash","icon","name","channel","nickname","ip","path",{"name":"size","type":"int"},{"name":"reg_date","type":"int"},{"name":"exp_date","type":"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						columns:[{
							text:Admin.getText("attachment/columns/name"),
							dataIndex:"name",
							width:200,
							sortable:true,
							renderer:function(value,p,record) {
								return '<i class="icon" style="background-image:url(' + record.data.icon + '); background-size:contain; background-repeat:no-repeat; background-position:50% 50%; margin-right:8px;"></i>' + value;
							}
						},{
							text:Admin.getText("attachment/columns/channel"),
							dataIndex:"channel",
							width:120,
							sortable:true,
							renderer:function(value) {
								return "#" + value;
							}
						},{
							text:Admin.getText("attachment/columns/nickname"),
							dataIndex:"nickname",
							width:120,
							sortable:true
						},{
							text:Admin.getText("attachment/columns/ip"),
							dataIndex:"ip",
							width:120,
							sortable:true,
							align:"center"
						},{
							text:Admin.getText("attachment/columns/size"),
							dataIndex:"size",
							width:100,
							sortable:true,
							align:"right",
							renderer:function(value) {
								return Minitalk.getFileSize(value);
							}
						},{
							text:Admin.getText("attachment/columns/path"),
							dataIndex:"path",
							minWidth:200,
							flex:1,
							sortable:true
						},{
							text:Admin.getText("attachment/columns/reg_date"),
							dataIndex:"reg_date",
							width:145,
							sortable:true,
							align:"center",
							renderer:function(value) {
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							}
						},{
							text:Admin.getText("attachment/columns/exp_date"),
							dataIndex:"exp_date",
							width:145,
							sortable:true,
							align:"center",
							renderer:function(value,p) {
								if (value * 1000 < moment().valueOf()) p.style = "color:#999;";
								if (value == 0) return "";
								
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							}
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:[
							new Ext.Button({
								iconCls:"x-tbar-loading",
								handler:function() {
									Ext.getCmp("MinitalkPanel-attachment").getStore().reload();
								}
							}),
							"->",
							{xtype:"tbtext",text:Admin.getText("attachment/grid_help")}
						],
						listeners:{
							itemdblclick:function(grid,record) {
								window.open(record.data.download);
							},
							itemcontextmenu:function(grid,record,item,index,e) {
								var menu = new Ext.menu.Menu();
								
								menu.addTitle(record.data.domain);
								
								menu.add({
									text:Admin.getText("attachment/delete"),
									iconCls:"mi mi-trash",
									handler:function() {
										Admin.attachment.delete();
									}
								});
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
					new Ext.grid.Panel({
						id:"MinitalkPanel-banip",
						tbar:[
							new Ext.form.TextField({
								id:"MinitalkIpKeyword",
								width:200,
								emptyText:Admin.getText("banip/columns/ip") + " / " + Admin.getText("banip/columns/nickname"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkIpSearchButton").handler();
										}
									}
								}
							}),
							new Ext.Button({
								id:"MinitalkIpSearchButton",
								iconCls:"mi mi-search",
								handler:function() {
									Ext.getCmp("MinitalkPanel-banip").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MinitalkIpKeyword").getValue());
									Ext.getCmp("MinitalkPanel-banip").getStore().loadPage(1);
								}
							}),
							"-",
							new Ext.Button({
								text:Admin.getText("banip/add"),
								iconCls:"mi mi-plus",
								handler:function() {
									Admin.banip.add();
								}
							}),
							new Ext.Button({
								text:Admin.getText("banip/delete"),
								iconCls:"mi mi-trash",
								handler:function() {
									Admin.banip.delete();
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getBanIps"),
								reader:{type:"json"}
							},
							remoteSort:true,
							sorters:[{property:"reg_date",direction:"DESC"}],
							autoLoad:true,
							pageSize:50,
							fields:["ip","nickname","memo",{name:"reg_date",type:"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						columns:[{
							header:Admin.getText("banip/columns/ip"),
							dataIndex:"ip",
							width:140
						},{
							header:Admin.getText("banip/columns/nickname"),
							dataIndex:"nickname",
							width:200
						},{
							header:Admin.getText("banip/columns/memo"),
							dataIndex:"memo",
							minWidth:200,
							flex:1
						},{
							header:Admin.getText("banip/columns/reg_date"),
							dataIndex:"reg_date",
							width:140,
							renderer:function(value) {
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
							}
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:new Ext.PagingToolbar({
							store:null,
							displayInfo:false,
							items:[
								"->",
								{xtype:"tbtext",text:Admin.getText("banip/grid_help")}
							],
							listeners:{
								beforerender:function(tool) {
									tool.bindStore(tool.ownerCt.getStore());
								}
							}
						}),
						listeners:{
							itemdblclick:function(grid,record) {
								Admin.banip.add(record.data.ip);
							},
							itemcontextmenu:function(grid,record,item,index,e) {
								var menu = new Ext.menu.Menu();
								
								menu.addTitle(record.data.ip);
								
								menu.add({
									text:Admin.getText("banip/modify"),
									iconCls:"xi xi-form",
									handler:function() {
										Admin.banip.add(record.data.ip);
									}
								});
								
								menu.add({
									text:Admin.getText("banip/delete"),
									iconCls:"mi mi-trash",
									handler:function() {
										Admin.banip.delete();
									}
								});
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
					<?php if ($hasServer == true) { ?>
					new Ext.Panel({
						id:"MinitalkPanel-history",
						autoScroll:true,
						tbar:[
							new Ext.Button({
								iconCls:"fa fa-caret-left",
								handler:function() {
									var date = Ext.getCmp("MinitalkHistoryDate").getValue();
									var move = moment(date).add(-1,"day");
									Ext.getCmp("MinitalkHistoryDate").setValue(move.format("YYYY-MM-DD"));
								}
							}),
							new Ext.form.DateField({
								id:"MinitalkHistoryDate",
								format:"Y-m-d",
								width:115,
								value:moment().format("YYYY-MM-DD"),
								listeners:{
									change:function(form,value) {
										var current = moment(value);
										if (current.isValid() == true) {
											Ext.getCmp("MinitalkPanel-history").store.getProxy().setExtraParam("date",current.format("YYYY-MM-DD"));
											Ext.getCmp("MinitalkPanel-history").store.loadPage(1);
										}
									}
								}
							}),
							new Ext.Button({
								iconCls:"fa fa-caret-right",
								handler:function() {
									var date = Ext.getCmp("MinitalkHistoryDate").getValue();
									var move = moment(date).add(1,"day");
									Ext.getCmp("MinitalkHistoryDate").setValue(move.format("YYYY-MM-DD"));
								}
							}),
							"-",
							new Ext.form.TextField({
								id:"MinitalkHistoryChannel",
								width:120,
								emptyText:Admin.getText("history/channel"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkHistorySearchButton").handler();
										}
									}
								}
							}),
							new Ext.form.TextField({
								id:"MinitalkHistoryNickname",
								width:120,
								emptyText:Admin.getText("history/nickname"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkHistorySearchButton").handler();
										}
									}
								}
							}),
							new Ext.form.TextField({
								id:"MinitalkHistoryKeyword",
								width:140,
								emptyText:Admin.getText("history/keyword"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkHistorySearchButton").handler();
										}
									}
								}
							}),
							new Ext.Button({
								id:"MinitalkHistorySearchButton",
								iconCls:"mi mi-search",
								handler:function() {
									Ext.getCmp("MinitalkPanel-history").store.getProxy().setExtraParam("channel",Ext.getCmp("MinitalkHistoryChannel").getValue());
									Ext.getCmp("MinitalkPanel-history").store.getProxy().setExtraParam("nickname",Ext.getCmp("MinitalkHistoryNickname").getValue());
									Ext.getCmp("MinitalkPanel-history").store.getProxy().setExtraParam("keyword",Ext.getCmp("MinitalkHistoryKeyword").getValue());
									Ext.getCmp("MinitalkPanel-history").store.loadPage(1);
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getHistory"),
								extraParams:{date:moment().format("YYYY-MM-DD")},
								reader:{type:"json"}
							},
							remoteSort:true,
							sorters:[{property:"time",direction:"ASC"}],
							pageSize:50,
							fields:["user","time","channel","nickname","message","ip"],
							listeners:{
								load:function(store,records,success,e) {
									if (success == true) {
										$("#MinitalkHistoryTotalRows").html(Ext.util.Format.number(store.getTotalCount(),"0,000"));
										
										var $panel = $("#MinitalkPanel-history-innerCt");
										$panel.empty();
										for (var i=0, loop=store.getCount();i<loop;i++) {
											var item = store.getAt(i).data;
											
											var $item = $("<div>");
											$item.css("padding","5px");
											
											var $time = $("<time>");
											$time.css("color","#666").css("paddingRight","5px");
											$time.html("[" + moment(item.time).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss") + "]");
											$item.append($time);
											
											var $channel = $("<u>");
											$channel.css("fontWeight","bold").css("color","#2196F3").css("paddingRight","5px");
											$channel.html("#" + item.room);
											$item.append($channel);
											
											var $photo = $("<i>").addClass("message photo");
											if (item.user.photo) $photo.css("backgroundImage","url(" + item.user.photo + ");");
											$item.append($photo);
											
											if (item.to == null) {
												var $user = $("<b>");
												$user.html(item.nickname);
												$item.append($user);
											} else {
												var $user = $("<b>").css("color","#ff00ff");
												$user.html(item.nickname + ' <i class="mi mi-right"></i> ' + item.to.nickname);
												$item.append($user);
											}
											
											var $message = $("<span>");
											if (item.to !== null) $message.css("color","#ff00ff");
											if (item.type == "message") {
												$message.html(' : ' + item.message);
											} else if (item.type == "file") {
												$message.html(' : <i class="mi mi-download"></i> <a href="' + item.data.download + '">' + item.message + ' (' + Minitalk.getFileSize(item.data.size) + ')</a>');
											}
											$item.append($message);
											
											var $ip = $("<label>");
											$ip.css("color","#999").css("fontFamily","OpenSans").css("fontSize","11px");
											$ip.html(" (" + item.ip + ")");
											$item.append($ip);
											
											$panel.append($item);
										}
									} else {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						html:'',
						bbar:new Ext.PagingToolbar({
							store:null,
							displayInfo:false,
							items:[
								"->",
								{xtype:"tbtext",text:'<span style="font-family:OpenSans;">Total <span id="MinitalkHistoryTotalRows" style="font-weight:bold;">0</span> History</span>'}
							],
							listeners:{
								beforerender:function(tool) {
									tool.bindStore(tool.ownerCt.store);
								}
							}
						}),
						listeners:{
							show:function() {
								if (Ext.getCmp("MinitalkPanel-history").store.isLoaded() == false && Ext.getCmp("MinitalkPanel-history").store.isLoading() == false) {
									Ext.getCmp("MinitalkPanel-history").store.loadPage(1);
								}
							}
						}
					}),
					new Ext.grid.Panel({
						id:"MinitalkPanel-broadcast",
						tbar:[
							new Ext.form.TextField({
								id:"MinitalkBroadcastKeyword",
								width:200,
								emptyText:Admin.getText("broadcast/columns/message"),
								enableKeyEvents:true,
								listeners:{
									keypress:function(form,e) {
										if (e.keyCode == 13) {
											Ext.getCmp("MinitalkBroadcastSearchButton").handler();
										}
									}
								}
							}),
							new Ext.Button({
								id:"MinitalkBroadcastSearchButton",
								iconCls:"mi mi-search",
								handler:function() {
									Ext.getCmp("MinitalkPanel-broadcast").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("MinitalkBroadcastKeyword").getValue());
									Ext.getCmp("MinitalkPanel-broadcast").getStore().loadPage(1);
								}
							}),
							"-",
							new Ext.Button({
								text:Admin.getText("broadcast/send"),
								iconCls:"xi xi-paper-plane",
								handler:function() {
									Admin.broadcast.send();
								}
							}),
							new Ext.Button({
								text:Admin.getText("broadcast/delete"),
								iconCls:"mi mi-trash",
								handler:function() {
									Admin.broadcast.delete();
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:Minitalk.getProcessUrl("@getBroadcasts"),
								reader:{type:"json"}
							},
							remoteSort:true,
							sorters:[{property:"reg_date",direction:"DESC"}],
							autoLoad:true,
							pageSize:50,
							fields:["id","type","message","nickname","url",{name:"receiver",type:"int"},{name:"reg_date",type:"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
										if (e.getError()) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
										}
									}
								}
							}
						}),
						columns:[{
							header:Admin.getText("broadcast/columns/type"),
							dataIndex:"type",
							width:100,
							align:"center",
							renderer:function(value,p) {
								if (value == "NOTICE") p.style = "color:red;";
								else p.style = "color:blue;";
								return Admin.getText("broadcast/type/"+value);
							}
						},{
							header:Admin.getText("broadcast/columns/channel"),
							dataIndex:"channel",
							width:100
						},{
							header:Admin.getText("broadcast/columns/message"),
							dataIndex:"message",
							minWidth:100,
							flex:1,
							renderer:function(value,p,record) {
								if (record.data.type == "NOTICE") return value;
								
								var sHTML = "";
								if (value.user.photo) {
									sHTML+= '<i class="message photo" style="background-image:url(' + value.user.photo + ');"></i>';
								} else {
									sHTML+= '<i class="message photo"></i>';
								}
								sHTML+= "<b>" + value.user.nickname + "</b> : " + value.message;
								return sHTML;
							}
						},{
							header:Admin.getText("broadcast/columns/url"),
							dataIndex:"url",
							width:250
						},{
							header:Admin.getText("broadcast/columns/receiver"),
							dataIndex:"receiver",
							width:100,
							align:"right",
							renderer:function(value,p,record) {
								if (record.data.type == "NOTICE") return Ext.util.Format.number(value,"0,000");
								return "UNKNOWN";
							}
						},{
							header:Admin.getText("broadcast/columns/reg_date"),
							dataIndex:"reg_date",
							width:160,
							align:"center",
							renderer:function(value) {
								return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss");
							}
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:new Ext.PagingToolbar({
							store:null,
							displayInfo:false,
							items:[
								"->",
								{xtype:"tbtext",text:Admin.getText("broadcast/grid_help")}
							],
							listeners:{
								beforerender:function(tool) {
									tool.bindStore(tool.ownerCt.getStore());
								}
							}
						}),
						listeners:{
							itemdblclick:function(grid,record) {
								Admin.broadcast.send(record.data);
							},
							itemcontextmenu:function(grid,record,item,index,e) {
								var menu = new Ext.menu.Menu();
								
								menu.addTitle(record.data.message);
								
								menu.add({
									text:Admin.getText("broadcast/resend"),
									iconCls:"xi xi-reply",
									handler:function() {
										Admin.broadcast.send(record.data);
									}
								});
								
								menu.add({
									text:Admin.getText("broadcast/delete"),
									iconCls:"mi mi-trash",
									handler:function() {
										Admin.broadcast.delete();
									}
								});
								
								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
					<?php } ?>
					null
				],
				listeners:{
					render:function(tab) {
						tab.getTabBar().setVisible(false);
					},
					afterRender:function(tabs) {
						if (Ext.getCmp("MinitalkTabPanel").getActiveTab().getId() == "MinitalkPanel-<?php echo $current; ?>") {
							tabs.fireEvent("tabchange",tabs,Ext.getCmp("MinitalkTabPanel").getActiveTab());
						} else {
							Ext.getCmp("MinitalkTabPanel").setActiveTab("MinitalkPanel-<?php echo $current; ?>");
						}
					},
					tabchange:function(tabs,tab) {
						var panel = tab.getId().split("-").pop();
						$("#MinitalkHeader li.selected").removeClass("selected");
						$("#MinitalkHeader button[data-tab="+panel+"]").parent().addClass("selected");
						
						var title = $("#MinitalkHeader button[data-tab="+panel+"]").text() + " - " + $("title").text().split(" - ").pop();
						history.pushState({panel:panel},title,"<?php echo __MINITALK_DIR__; ?>/admin/" + panel);
						document.title = title;
					}
				}
			}),
			new Ext.Panel({
				region:"south",
				height:25,
				border:false,
				contentEl:"MinitalkFooter"
			})
		],
		listeners:{
			afterRender:function() {
				$(document).triggerHandler("MinitalkAdminReady");
			},
			resize:function() {
				var $header = $("#MinitalkHeader");
				var $menus = $("ul[data-role=menu]",$header);
				var $items = $("> li",$menus);
				var $lists = $("> li[data-role=more] > ul",$menus);
				$lists.empty();
				$items.show();
				
				var count = 0;
				var limitWidth = $menus.innerWidth();
				var currentWidth = 0;
				var is_more = false;
				$items.each(function() {
					var width = $(this).outerWidth();
					if (currentWidth + width + 40 > limitWidth) {
						is_more = true;
						return false;
					}
					
					currentWidth+= width;
					count++;
				});
				
				if (is_more == true) {
					for (var i=loop=$items.length - 1;i>=Math.max(1,count);i--) {
						var $menu = $items.eq(i).clone(true);
						
						$lists.prepend($menu);
						$items.eq(i).hide();
					}
				} else {
					$items.eq(0).hide();
				}
			}
		}
	}).updateLayout();
	
	try {
		document.fonts.ready.then(function() {
			setTimeout(function() { Ext.getCmp("AdminViewport").updateLayout(); Ext.getCmp("AdminViewport").fireEvent("resize"); },1000);
		});
	} catch (e) {}
});

$(document).ready(function() {
	$("#MinitalkHeader ul[data-role=menu] > li[data-role=more] > button").on("click",function(e) {
		$(this).parent().toggleClass("on");
		e.stopPropagation();
	});
});

$(document).on("click",function() {
	$("#MinitalkHeader ul[data-role=menu] > li[data-role=more]").removeClass("on");
});
</script>
<?php } ?>
</body>
</html>