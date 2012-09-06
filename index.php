<?php
	//Has a thread been sent?
	if (isset($_GET['thread']))
	{
		$thread_id = htmlspecialchars($_GET["thread"]);
	} else {
		$thread_id = "260466"; // Default Thread
	}

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>HyperThread - App.net Thread Viewer</title>

	<!-- CSS Files -->
	<link type="text/css" href="css/base.css" rel="stylesheet" />

	<!--[if IE]><script language="javascript" type="text/javascript" src="../../Extras/excanvas.js"></script><![endif]-->

	<!-- JIT Library File -->
	<script language="javascript" type="text/javascript" src="js/jit.js"></script>

	<!-- HyperTree -->
	<script language="javascript" type="text/javascript" src="js/hypertree.js.php?thread=<?php echo $thread_id; ?>"></script>
</head>

<body onload="init();">

	<div id="container">

		<div id="center-container">
			<div id="infovis">
			</div>
		</div>

		<div id="left-container">

			<div class="text">
				<h4>HyperThread</h4> 
				An expermental way to view threads on App.net
			</div>
			<div id="thread-form">	
				<form name="thread" action="" method="get">
					Thread ID: <input type="text" name="thread" />
					<input type="submit" value="HyperThread It!" />
				</form>
				Here are some interesting threads for you to try out:
				<ul>
					<li><a href="?thread=262171">262171</a></li>
					<li><a href="?thread=242969">242969</a></li>
					<li><a href="?thread=263761">263761</a></li>
					<li><a href="?thread=243022">243022</a></li>
					<li><a href="?thread=265845">265845</a></li>
				</ul>

			</div>
			<div id="explanations">
				<h3>Note:</h3>
				<b>Use the mouse wheel</b> to zoom and <b>drag and drop the canvas</b> to pan.<br><br>
				This is a rough-and-ready prototype &amp; may not work with threads in excess of 200 posts.<br>
				Built using <a href="http://thejit.org/">The JavaScript InfoVis Toolkit</a>.<br><br>
				<a href="https://github.com/edent/HyperThread">OpenSource HyperThread on GitHub</a><br><br>
				HyperThread created by <a href="http://shkspr.mobi/blog/">Terence Eden</a>.  You can follow me on <a href="http://alpha.app.net/edent">App.net</a> and on <a href="https://twitter.com/edent">Twitter</a>.
			</div>
		</div>


		<div id="right-container">
			<div id="inner-details"></div>
		</div>

		<div id="log"></div>
	</div>
</body>
</html>