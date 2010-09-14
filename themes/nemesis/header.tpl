<!-- BEGIN: HEADER -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>{HEADER_TITLE}</title> 
<!-- IF {HEADER_META_DESCRIPTION} --><meta name="description" content="{HEADER_META_DESCRIPTION}" /><!-- ENDIF -->
<!-- IF {HEADER_META_KEYWORDS} --><meta name="keywords" content="{HEADER_META_KEYWORDS}" /><!-- ENDIF -->
<meta http-equiv="content-type" content="{HEADER_META_CONTENTTYPE}; charset={HEADER_META_CHARSET}" />
<meta name="generator" content="Cotonti http://www.cotonti.com" />
<meta http-equiv="expires" content="Fri, Apr 01 1974 00:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="last-modified" content="{HEADER_META_LASTMODIFIED} GMT" />
{HEADER_HEAD}
{HEADER_BASEHREF}
<link rel="shortcut icon" href="favicon.ico" />
<link href="skins/{PHP.skin}/css/{PHP.theme}.css" type="text/css" rel="stylesheet" />
{HEADER_COMPOPUP}

</head>

<body>

<div id="container">

	<h1><a href="{PHP.cfg.mainurl}" title="{PHP.cfg.maintitle} {PHP.cfg.separator} {PHP.cfg.subtitle}">{PHP.cfg.maintitle}</a></h1>
	<p class="small subtitle">{PHP.cfg.subtitle}</p>

	<ul id="nav">
		<li>
			<a href="{PHP.cfg.mainurl}" title="{PHP.L.Home}">
				{PHP.L.Home}
				<span>Start here</span>
			</a>
		</li>
		<li>
			<a href="index.php?z=forums" title="{PHP.L.Forums}">
				{PHP.L.Forums}
				<span>Discussions</span>
			</a>
		</li>
		<li>
			<a href="index.php?z=page&amp;c=news" title="{PHP.L.News}">
				{PHP.L.News}
				<span>Our updates</span>
			</a>
		</li>
		<li>
			<a href="index.php?z=rss" title="{PHP.L.RSS_Feeds}">
				RSS
				<span>Subscribe me</span>
			</a>
		</li>
	</ul>

	<!-- IF {PHP.z} == "index" --><img src="skins/{PHP.skin}/img/front_image.png" alt="" id="front_image" /><!-- ENDIF -->

	<div id="main">

<!-- END: HEADER -->