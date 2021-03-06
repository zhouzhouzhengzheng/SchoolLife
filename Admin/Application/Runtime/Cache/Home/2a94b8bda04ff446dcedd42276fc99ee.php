<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>网站后台系统</title>
	<link href="/SchoolAdmin/Public/style/authority/main_css.css" rel="stylesheet" type="text/css" />
	<link href="/SchoolAdmin/Public/style/authority/zTreeStyle.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="/scripts/jquery/jquery-1.7.1/SchoolAdmin/Public.js"></script>
	<script type="text/javascript" src="/SchoolAdmin/Public/scripts/zTree/jquery.ztree.core-3.2.js"></script>
	<script type="text/javascript" src="/SchoolAdmin/Public/scripts/authority/commonAll.js"></script>
	<script type="text/javascript">
		/* zTree插件加载目录的处理  */
		var zTree;
		
		var setting = {
				view: {
					dblClickExpand: false,
					showLine: false,
					expandSpeed: ($.browser.msie && parseInt($.browser.version)<=6)?"":"fast"
				},
				data: {
					key: {
						name: "resourceName"
					},
					simpleData: {
						enable:true,
						idKey: "resourceID",
						pIdKey: "parentID",
						rootPId: ""
					}
				},
				callback: {
	// 				beforeExpand: beforeExpand,
	// 				onExpand: onExpand,
					onClick: zTreeOnClick			
				}
		};
		 
		var curExpandNode = null;
		function beforeExpand(treeId, treeNode) {
			var pNode = curExpandNode ? curExpandNode.getParentNode():null;
			var treeNodeP = treeNode.parentTId ? treeNode.getParentNode():null;
			for(var i=0, l=!treeNodeP ? 0:treeNodeP.children.length; i<l; i++ ) {
				if (treeNode !== treeNodeP.children[i]) {
					zTree.expandNode(treeNodeP.children[i], false);
				}
			}
			while (pNode) {
				if (pNode === treeNode) {
					break;
				
				pNode = pNode.getParentNode();
			}
			if (!pNode) {
				singlePath(treeNode);
			}
	
		}
		function singlePath(newNode) {
			if (newNode === curExpandNode) return;
			if (curExpandNode && curExpandNode.open==true) {
				if (newNode.parentTId === curExpandNode.parentTId) {
					zTree.expandNode(curExpandNode, false);
				} else {
					var newParents = [];
					while (newNode) {
						newNode = newNode.getParentNode();
						if (newNode === curExpandNode) {
							newParents = null;
							break;
						} else if (newNode) {
							newParents.push(newNode);
						}
					}
					if (newParents!=null) {
						var oldNode = curExpandNode;
						var oldParents = [];
						while (oldNode) {
							oldNode = oldNode.getParentNode();
							if (oldNode) {
								oldParents.push(oldNode);
							}
						}
						if (newParents.length>0) {
							for (var i = Math.min(newParents.length, oldParents.length)-1; i>=0; i--) {
								if (newParents[i] !== oldParents[i]) {
									zTree.expandNode(oldParents[i], false);
									break;
								}
							}
						}else {
							zTree.expandNode(oldParents[oldParents.length-1], false);
						}
					}
				}
			}
			curExpandNode = newNode;
		}
	
		function onExpand(event, treeId, treeNode) {
			curExpandNode = treeNode;
		}
		
		/** 用于捕获节点被点击的事件回调函数  **/
		function zTreeOnClick(event, treeId, treeNode) {
			var zTree = $.fn.zTree.getZTreeObj("dleft_tab1");
			zTree.expandNode(treeNode, null, null, null, true);
	// 		zTree.expandNode(treeNode);
			// 规定：如果是父类节点，不允许单击操作
			if(treeNode.isParent){
	// 			alert("父类节点无法点击哦...");
				return false;
			}
			// 如果节点路径为空或者为"#"，不允许单击操作
			if(treeNode.accessPath=="" || treeNode.accessPath=="#"){
				//alert("节点路径为空或者为'#'哦...");
				return false;
			}
		    // 跳到该节点下对应的路径, 把当前资源ID(resourceID)传到后台，写进Session
		    rightMain(treeNode.accessPath);
		    
		    if( treeNode.isParent ){
			    $('#here_area').html('当前位置：'+treeNode.getParentNode().resourceName+'&nbsp;>&nbsp;<span style="color:#1A5CC6">'+treeNode.resourceName+'</span>');
		    }else{
			    $('#here_area').html('当前位置：系统&nbsp;>&nbsp;<span style="color:#1A5CC6">'+treeNode.resourceName+'</span>');
		    }
		};
		
		/* 上方菜单 */
		function switchTab(tabpage,tabid){
		var oItem = document.getElementById(tabpage).getElementsByTagName("li"); 
		    for(var i=0; i<oItem.length; i++){
		        var x = oItem[i];    
		        x.className = "";
			}			
				$(document).ajaxStart(onStart).ajaxSuccess(onStop);
				loadMenu(tabid, 'dleft_tab1');
		}
		
		
		$(document).ready(function(){
			$(document).ajaxStart(onStart).ajaxSuccess(onStop);
			/** 默认异步加载"业务模块"目录  **/
			loadMenu('Core', "dleft_tab1");
			// 默认展开所有节点
			if( zTree ){
				// 默认展开所有节点
				zTree.expandAll(true);
			}
		});
		
		function loadMenu(resourceType, treeObj){
			$.ajax({
				type:"POST",
				url:  "/SchoolAdmin/index.php/Home/Index/LeftMenu",
				dataType : "json",
				data: {resourceType:resourceType},
				success:function(data){
					// 如果返回数据不为空，加载"业务模块"目录
					if(data != null){
						// 将返回的数据赋给zTree
						$.fn.zTree.init($("#"+treeObj), setting, data);
 						//alert(treeObj);
						zTree = $.fn.zTree.getZTreeObj(treeObj);
						if( zTree ){
							// 默认展开所有节点
							zTree.expandAll(true);
						}
					}
				}
			});
		}
		
		//ajax start function
		function onStart(){
			$("#ajaxDialog").show();
		}
		
		//ajax stop function
		function onStop(){
	// 		$("#ajaxDialog").dialog("close");
			$("#ajaxDialog").hide();
		}
	</script>
</head>
<body onload="getDate01()">
    <div id="top">
		<div id="top_links">
			<div id="top_op">
				<ul>
					<li>
						<div style="color:#FFF; font-size:14px; background:url(/SchoolAdmin/Public/images/common/user.png) no-repeat; padding-left:24px; background-position:0px 22px;">：admin</div>
					</li>
					<li>
						<div style="color:#FFF; font-size:14px; background:url(/SchoolAdmin/Public/images/common/month.png) no-repeat; padding-left:24px; background-position:0px 22px;">：超级管理员</div>
					</li>
					<li>
						<div style="color:#FFF; font-size:14px; background:url(/SchoolAdmin/Public/images/common/date.png) no-repeat; padding-left:24px; background-position:0px 22px;">：2015-10-22</div>
					</li>
				</ul> 
			</div>
			<div id="top_close">
				<a href="/SchoolAdmin/index.php/Home/Index/Logout" target="_parent">
					<img alt="退出系统" title="退出系统" src="/SchoolAdmin/Public/images/common/close.png" style="position: relative; top: 6px; left: 25px;">
				</a>
			</div>
		</div>
	</div>
    <!-- side menu start -->
	<div id="side">
		<div id="left_menu">
		 	<ul id="TabPage2" style="height:200px; margin-top:50px;">
				<li id="left_tab1" class="selected" onClick="javascript:switchTab('TabPage2','Core');" title="核心管理">
					<img alt="核心管理" title="核心管理" src="/SchoolAdmin/Public/images/common/Core_hover.png" width="33" height="31">
				</li>

				<li id="left_tab3" onClick="javascript:switchTab('TabPage2','Member');" title="会员管理">
					<img alt="会员管理" title="会员管理" src="/SchoolAdmin/Public/images/common/member.png" width="33" height="31">
				</li>
				<li id="left_tab3" onClick="javascript:switchTab('TabPage2','Temp');" title="模板管理">
					<img alt="模板管理" title="模板管理" src="/SchoolAdmin/Public/images/common/temp.png" width="33" height="31">
				</li>
                
                <li id="left_tab3" onClick="javascript:switchTab('TabPage2','Plugin');" title="插件管理">
					<img alt="插件管理" title="插件管理" src="/SchoolAdmin/Public/images/common/plugin.png" width="33" height="31">
				</li>
                <li id="left_tab2" onClick="javascript:switchTab('TabPage2','System');" title="系统管理">
					<img alt="系统管理" title="系统管理" src="/SchoolAdmin/Public/images/common/system.png" width="33" height="31">
				</li>		
                <li id="left_tab3" onClick="javascript:switchTab('TabPage2','Extend');" title="应用扩展">
					<img alt="应用扩展" title="应用扩展" src="/SchoolAdmin/Public/images/common/Extend.png" width="33" height="31">
				</li>

			</ul>
			
			
			<div id="nav_show" style="position:absolute; bottom:0px; padding:10px;">
				<a href="javascript:;" id="show_hide_btn">
					<img alt="显示/隐藏" title="显示/隐藏" src="/SchoolAdmin/Public/images/common/nav_hide.png" width="35" height="35">
				</a>
			</div>
		 </div>
		 <div id="left_menu_cnt">
		 	<div id="nav_module">
		 		<span style="line-height:70px; width:175px; padding-left:10px; overflow:hidden; height:57px; display:block; font-size:18px; font-family:'微软雅黑'; border-bottom:1px solid #CCC; font-weight:bolder; color:#2F2F2F">内容板块 / Core</span>
		 	</div>
		 	<div id="nav_resource">
		 		<ul id="dleft_tab1" class="ztree"></ul>
		 	</div>
		 </div>
	</div>
	<script type="text/javascript">
		$(function(){
			$('#TabPage2 li').click(function(){
				var index = $(this).index();
				var Img = new Array();
				Img[0] = "Core";
				Img[1] = "Member";
				Img[2] = "Temp";
				Img[3] = "Plugin";
				Img[4] = "System";
				Img[5] = "Extend";
				$(this).find('img').attr('src', '/SchoolAdmin/Public/images/common/'+ Img[index] +'_hover.png');
				$(this).css({background:'#F1F1F1'});
				$('#nav_module').find('span').html($(this).attr("title")+" / "+Img[index]);
				$('#TabPage2 li').each(function(i, ele){
					if( i!=index ){
						$(ele).find('img').attr('src', '/SchoolAdmin/Public/images/common/'+ Img[i] +'.png');
						$(ele).css({background:'#2F2F2F'});
					}
				});
				// 显示侧边栏
				switchSysBar(true);
			});
			
			// 显示隐藏侧边栏
			$("#show_hide_btn").click(function() {
		        switchSysBar();
		    });
		});
		
		/**隐藏或者显示侧边栏**/
		function switchSysBar(flag){
			var side = $('#side');
	        var left_menu_cnt = $('#left_menu_cnt');
			if( flag==true ){	// flag==true
				left_menu_cnt.show(500, 'linear');
				side.css({width:'280px'});
				$('#top_nav').css({width:'77%', left:'304px'});
	        	$('#main').css({left:'280px'});
			}else{
		        if ( left_menu_cnt.is(":visible") ) {
					left_menu_cnt.hide(10, 'linear');
					side.css({width:'60px'});
		        	$('#top_nav').css({width:'100%', left:'60px', 'padding-left':'28px'});
		        	$('#main').css({left:'60px'});
		        	$("#show_hide_btn").find('img').attr('src', '/SchoolAdmin/Public/images/common/nav_show.png');
		        } else {
					left_menu_cnt.show(500, 'linear');
					side.css({width:'280px'});
					$('#top_nav').css({width:'77%', left:'304px', 'padding-left':'0px'});
		        	$('#main').css({left:'280px'});
		        	$("#show_hide_btn").find('img').attr('src', '/SchoolAdmin/Public/images/common/nav_hide.png');
		        }
			}
		}
	</script>
    <!-- side menu start -->

    <div id="main">
      	<iframe name="right" id="rightMain" src="/SchoolAdmin/index.php/Home/Index/Main" frameborder="no" scrolling="auto" width="100%" height="100%" allowtransparency="true"/>
    </div>
<div style="display:none"><script src='http://v7.cnzz.com/stat.php?id=155540&web_id=155540' language='JavaScript' charset='gb2312'></script></div>
</body>
</html>