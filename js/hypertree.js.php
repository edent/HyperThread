<?php
	header("Content-type: text/javascript");

	//	Get the data 
	require_once '../AppDotNetPHP/EZAppDotNet.php';
	require_once '../config.php';		
	global $app_clientId, $app_clientSecret, $adn_accessToken;
	
	$app = new EZAppDotNet($app_clientId,$app_clientSecret);

	// Setting a static access token so that people don't need to log in.
	$app->setAccessToken($adn_accessToken);
	$app->setSession();

	//Has a thread been sent?
	if (isset($_GET['thread']))
	{
		$thread_id = htmlspecialchars($_GET["thread"]);
	} else {
		$thread_id = "260466"; // Default Thread
	}
?>
var labelType, useGradients, nativeTextSupport, animate;

function htmlDecode(input){
	var e = document.createElement('div');
	e.innerHTML = input;
	return e.childNodes[0].nodeValue;
}

(function() 
{
	var ua = navigator.userAgent,
				iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
				typeOfCanvas = typeof HTMLCanvasElement,
				nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
				textSupport = nativeCanvasSupport 
			&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');

	//I'm setting this based on the fact that ExCanvas provides text support for IE
	//and that as of today iPhone/iPad current text support is lame
	labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
	nativeTextSupport = labelType == 'Native';
	useGradients = nativeCanvasSupport;
	animate = !(iStuff || !nativeCanvasSupport);
})();

var Log = {
	elem: false,
		write: function(text){
			if (!this.elem) 
				this.elem = document.getElementById('log');
		this.elem.innerHTML = text;
		this.elem.style.left = (500 - this.elem.offsetWidth / 2) + 'px';
	}
};

function init(){

	<?php
		if ($app->getSession()) 
		{
			//	Get up to 200 replies
			$thread = $app->getPostReplies($thread_id,array('count'=>"200"));

			//	Get the head of the conversation
			$thread[] = $app->getPost($thread_id);

			//	Encode the string
			$json_string = json_encode($thread);

			//	Decode the string
			$data=json_decode($json_string,true);

			// first throw everything into an associative array for easy access
			$array	= array();
			$references = array();

			//	Iterate through the data, getting every individual post
			foreach ($data as $post) 
			{
				//	Get the data from the post
				$id = $post['id'];
				$post['children'] = array();	//	Children is an empty array

				$parent = $post['reply_to'];
				$text = $post['text'];
				$textHTML = $post['html'];
				$user = $post['user']['username'];
				$name = $post['user']['name'];
				$avatar = $post['user']['avatar_image']['url'];

				//	Place the post in the array based on its ID
				$references[$id] = array(
					"id" => $id,
					"name" => htmlspecialchars($name,ENT_QUOTES),
					"reply_to" => $parent, 
					"data" => array(
						"text" => htmlspecialchars($text,ENT_QUOTES), 
						"textHTML" => htmlspecialchars($textHTML,ENT_QUOTES),
						"user" => htmlspecialchars($user,ENT_QUOTES), 
						"avatar" => $avatar,
						'$direction' => array($id,$parent)
					)
				);
			}

			// now create the tree
			$tree = array();

			//	Iterate through the references, using & means this is a reference,
			//	http://php.net/manual/en/language.references.php
			foreach ($references as &$post) 
			{
				$id = $post['id'];
				$parentId = $post['reply_to'];
				// if it's a top level object, add it to the tree
				if (!$parentId) 
				{
					$tree[] =& $references[$id];
				}
				// else add it to the parent
				else 
				{
					$references[$parentId]['children'][] =& $post;
				}
				// avoid bad things by clearing the reference
				unset($post);
			}

			//	Trim the "[" and "]"
			$output = json_encode($tree);
			$output = substr($output, 1, -1);

			//	Sometimes a weird extra ",null" is added
			$needle = ",null";
			$length = strlen($needle);

			if (substr($output, -$length) === $needle)
			{
				$output = substr($output, 0, -$length);
			}


			
			//	Output into the JavaScript
			echo "\nvar json = " . $output . ";";
		}
		else
		{echo "No SESH!";
			echo "var json =;";
		}
?>
		var rgraph = new $jit.RGraph({
		//Where to append the visualization
		injectInto: 'infovis',
hideLabels:false,
		//Enable tips
		Tips: {
			enable: true,
			//add positioning offsets
			offsetX: 20,
			offsetY: 20,
			//implement the onShow method to
			//add content to the tooltip when a node
			//is hovered
			onShow: function(tip, node, isLeaf, domElement) {
			var html =	"<div class=\"tip-title\">" + 
							node.name + " said:"+
						"</div>"+
						"<div>"+
							node.data.text+
						"</div>";
			
			tip.innerHTML =	html; 
			}	
		},

		//Optional: create a background canvas that plots
		//concentric circles.
		background: {
			CanvasStyles: {
			strokeStyle: '#555'
			}
		},
		//Add navigation capabilities:
		//zooming by scrolling and panning.
		Navigation: {
			enable: true,
			panning: true,
			zooming: 10
		},
		//Set Node and Edge styles.
		Node: {
			color: '#ddeeff'
		},
		
		Edge: {
			color: '#C17878',
			type: 'arrow',
			lineWidth:1.5
		},

		onBeforeCompute: function(node){
			console.log("before compute");
			Log.write("Focusing on " + node.name + "...");
			//Add details to the right column.
			//$jit.id('inner-details').innerHTML = node.data.relation;
		},

		onAfterCompute: function(){
			console.log("aftercompute");
			var node = rgraph.graph.getClosestNodeToOrigin("current");
			var html = "<h4>" + 
						node.name + 
						"</h4>"+
						"<img src='"+ node.data.avatar + "?w=150' width=150 height=150 /><br>"+
						htmlDecode(node.data.textHTML)+""+
						"<hr>"+
						"<a href='https://alpha.app.net/"+node.data.user+"/post/"+node.id+"' rel='external nofollow' target='_blank'>Reply To Post</a>";

			$jit.id('inner-details').innerHTML = html;
			Log.write("");
		},
		//Add the name of the node in the correponding label
		//and a click handler to move the graph.
		//This method is called once, on label creation.
		onCreateLabel: function(domElement, node){
			//	Set background image to be the avatar
			domElement.style.background='url('+node.data.avatar+'?w=40'+') no-repeat center';

			//	Set Text
			domElement.innerHTML = '<br><br><br><br>'+
							node.name+'&nbsp;&nbsp;&nbsp;'; // Cludge to ensure div is wide enought

			domElement.onclick = function(){
				rgraph.onClick(node.id);
				hideLabels: false;
			};
		},
		//Change some label dom properties.
		//This method is called each time a label is plotted.
		onPlaceLabel: function(domElement, node){
			var style = domElement.style;
			style.display = '';
			style.cursor = 'pointer';

			if (node._depth <= 1) {
				style.fontSize = "0.8em";
				style.color = "#ccc";
			
			} else if(node._depth == 2){
				style.fontSize = "0.7em";
				style.color = "#494949";
			
			} else if(node._depth == 3){
				style.fontSize = "0.6em";
				style.color = "#494949";
			
			} else if(node._depth == 4){
				style.fontSize = "0.5em";
				style.color = "#494949";
			
			} else if(node._depth == 5){
				style.fontSize = "0.4em";
				style.color = "#494949";
			
			} else if(node._depth == 6){
				style.fontSize = "0.3em";
				style.color = "#494949";
			
			} else {
				style.display = 'none';
			}

			var left = parseInt(style.left);
			var w = domElement.offsetWidth;
			style.left = (left - w / 2) + 'px';
		}
	});
	//load JSON data
	rgraph.loadJSON(json);
	//trigger small animation
	rgraph.graph.eachNode(function(n) {
		var pos = n.getPos();
		pos.setc(-200, -200);
	});
	rgraph.compute('end');
	rgraph.fx.animate({
		modes:['polar'],
		duration: 3000,
		hideLabels:false
	});
}