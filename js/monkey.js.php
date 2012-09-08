<?php
	header("Content-type: text/javascript");

	//Has a thread been sent?
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
					"name" => htmlspecialchars($name),
					"reply_to" => $parent, 
					"data" => array(
						"text" => htmlspecialchars($text), 
						"textHTML" => htmlspecialchars($textHTML),
						"user" => htmlspecialchars($user), 
						"avatar" => $avatar
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
			
			//	Output into the JavaScript
			echo "var json = " . $output . ";";
		}
		else
		{echo "No SESH!";
			echo "var json =;";
		}
?>
    //init Spacetree
    //Create a new ST instance
    var st = new $jit.ST({
        //id of viz container element
        injectInto: 'infovis',
		orientation: 'top', 
		levelsToShow:'20',
		offsetY:100,
		siblingOffset:20,
        //set duration for the animation
        duration: 800,
        //set animation transition type
        transition: $jit.Trans.Quart.easeInOut,
        //set distance between node and its children
        levelDistance: 50,
        //enable panning
        Navigation: {
          enable:false
        },
        //set node and edge styles
        //set overridable=true for styling individual
        //nodes or edges
        Node: {
            height: 50,  
  			width: 50,
            type: 'circle',
            color: '#aaa',
            overridable: true
        },
        
        Edge: {
            type: 'bezier',
            overridable: true
        },
        
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

        onBeforeCompute: function(node){
            Log.write("loading " + node.name);
        },
        
        onAfterCompute: function(){
            Log.write("");

            //	Draw a red circle around the node on which we're focussing
            //get a reference to the canvas
			var canvas = document.getElementsByTagName('canvas')[0];
			//var canvas = document.getElementById('infovis').get(0);
			var ctx = canvas.getContext('2d');
			//draw a circle
			ctx.fillStyle = "#F7977A";
			ctx.beginPath();
			ctx.arc(5, -95, 42, 0, Math.PI*2, true);
			ctx.closePath();
			ctx.fill();

        },
        
        //This method is called on DOM label creation.
        //Use this method to add event handlers and styles to
        //your node.
        onCreateLabel: function(label, node){
            label.id = node.id;       

			//	Set background image to be the avatar
			label.style.background='url('+node.data.avatar+'?w=60&h60'+') no-repeat center';
			label.style.textAlign='center';
			//	Count how many direct replies
			var count = 0;
			node.eachSubnode(function(n) { count++; });

			if (count > 0) //	No replies means no sub-replies. Don't display anything
			{
				var countSub = 0;
				node.eachSubgraph(function(n) { countSub++; });

				//	Set Text
				label.innerHTML = '<br><br><br><br><br>' + count + " | " + (countSub - 1) + "&nbsp;"; // Cludge to ensure text appears below image		
			}


			//	Add post to right hand container
            label.onclick = function(){
            	st.onClick(node.id);

					    
				//	Better make myself look *big*
				/*
				bigSize = 75;
				var style = label.style;
				
				style.background='url('+node.data.avatar+'?w='+bigSize+'&h='+bigSize+') no-repeat center';
            	style.width = bigSize + 'px';
            	style.height = bigSize + 'px';            
	            style.fontSize = '1em';
	            node.data.$width = bigSize;
	            node.data.$height = bigSize;
            	*/
            	var html = "<h4>" + 
						"<a href='https://alpha.app.net/"+node.data.user+"' rel='external nofollow' target='_blank'>"+node.name+"</a>" + 
						"</h4>"+
						"<img src='"+ node.data.avatar + "?w=150' width=150 height=150 /><br>"+
						htmlDecode(node.data.textHTML)+""+
						"<hr>"+
						"<a href='https://alpha.app.net/"+node.data.user+"/post/"+node.id+"' rel='external nofollow' target='_blank'>Reply To Post</a>";
				$jit.id('inner-details').innerHTML = html;
            };

            //set label styles
            var style = label.style;
            style.width = 60 + 'px';
            style.height = 60 + 'px';            
            style.cursor = 'pointer';
            style.color = '#aaf';
            style.fontSize = '0.8em';
            style.textAlign= 'center';
            //style.paddingTop = '3px';
        },
        
        //This method is called right before plotting
        //a node. It's useful for changing an individual node
        //style properties before plotting it.
        //The data properties prefixed with a dollar
        //sign will override the global node style properties.
        onBeforePlotNode: function(node){
            //add some color to the nodes in the path between the
            //root node and the selected node.
            if (node.selected) {
                node.data.$color = "#f00";
            }
            else {
                delete node.data.$color;
               	
                //if the node belongs to the last plotted level
                if(!node.anySubnode("exist")) {
                    //count children number
                    var count = 0;
                    node.eachSubnode(function(n) { count++; });
                    //assign a node color based on
                    //how many children it has
                    node.data.$color = ['#aaa', '#baa', '#caa', '#daa', '#eaa', '#faa'][count];                    
                }
            }
        },
        
        //This method is called right before plotting
        //an edge. It's useful for changing an individual edge
        //style properties before plotting it.
        //Edge data proprties prefixed with a dollar sign will
        //override the Edge global style properties.
        onBeforePlotLine: function(adj){
            if (adj.nodeFrom.selected && adj.nodeTo.selected) {
                adj.data.$color = "#afa";
                adj.data.$lineWidth = 4;
            }
            else {
                delete adj.data.$color;
                delete adj.data.$lineWidth;
            }
        }
    });
    //load json data
    st.loadJSON(json);
    

    //compute node positions and layout
    st.compute();



    //optional: make a translation of the tree
    st.geom.translate(new $jit.Complex(-200, 0), "current");
    //emulate a click on the root node.
    st.onClick(st.root);
    //end
    //Add event handlers to switch spacetree orientation.
    var top = $jit.id('r-top'), 
        left = $jit.id('r-left'), 
        bottom = $jit.id('r-bottom'), 
        right = $jit.id('r-right'),
        normal = $jit.id('s-normal');
        
    
    function changeHandler() {
        if(this.checked) {
            top.disabled = bottom.disabled = right.disabled = left.disabled = true;
            st.switchPosition(this.value, "animate", {
                onComplete: function(){
                    top.disabled = bottom.disabled = right.disabled = left.disabled = false;
                }
            });
        }
    };
    
    top.onchange = left.onchange = bottom.onchange = right.onchange = changeHandler;
    //end

}
