<?php
    /*Optimize page loads by rendering restapi queries to static json files and save them in app/json/*/
/*
  ===BEWARE OF REST API PAGINATION AND SORT ORDER!====
Pagination:
Keep in mind, the rest API has a default of 16 records, so you have to set the parameter
&per_page=, and the limit is 100. If you need to return more than 100 results from any of the queries below
you have to paginate the results
Otherwise, the results you want, may not be the results it returns.
Sort: For sanity's sake, it's best that you sort posts by ID, so when inspecting your endpoint, they are in order
Hence, the REST_post_filter variable below.
*/
$GLOBALS['REST_post_filter'] = "filter[orderby]=ID&order=asc&per_page=100";// handles order and pagination

$GLOBALS['REST_CONFIG'] =array(//An array of url arguments
            "posts"=>"fields=id,type,title,content,slug,excerpt,languages,featured_media,screen_images,video,type,cats,tags&".$GLOBALS['REST_post_filter'],
            "pages"=>"fields=id,type,title,content,slug,excerpt,languages,featured_media,screen_images,featured_video,cats,tags,type&".$GLOBALS['REST_post_filter'],
            "project"=>"fields=id,type,title,content,slug,excerpt,languages,project_info,featured_media,screen_images,featured_video,type,cats,tags&".$GLOBALS['REST_post_filter'],
            "categories"=>"fields=id,name,count,slug,description,posts,children",
            "tags"=>"fields=id,name,slug,posts&".$GLOBALS['REST_post_filter'],
            "menus"=>"menus",
            "media"=>"fields=id,data&".$GLOBALS['REST_post_filter']
        );

// for WPML Comment this out if you aren't using it.
require_once("functions-wpml-languages.php");

        
    function getEndpoints(){ // BUILDS URLS FOR REST API ENDPOINTS

       $content = array();

        $url_path = "http://".$_SERVER['HTTP_HOST']."/wp-json/wp/v2/";//pendpoint path
        $server_path = get_template_directory()."/app/json/";//destination folder for writing
 
        if(@$_GET['endpoints']){//header for list of endpoints
                print "<strong>ENDPOINTS:</strong>
                <ul>";
        }
        
        foreach($GLOBALS['REST_CONFIG'] as $key => $value){//loops through the array of endpoints above

           $url = $url_path.$key."?".$value; // default, value passes params in REST_CONFIG array
     
            if(function_exists('icl_object_id')){// if WPML is here. 
                if($value == 'language'){ //language = $key, will not work with arguments
                    //see path registrations in WPML Languages
                    $url = $url_path.$key;// this is the REST API url with the language last
                }

            }

           
           $server = $server_path.$key.".json";
           if(@$_GET['publish']){
            

            $content[$key] = json_decode(getJSON($url));

           // writeJSON($server,)$content[$key];
           }

              if(@$_GET['endpoints']){//prints endpoing urls
                print "<li><a href='$url'>$key</a><br></li>";
              }

            
        }
        if(@$_GET['endpoints']){
            print "</ul>";
            die();//kills the page load so you can see the endpoint urls
        }
        if(@$_GET['publish']){
            header('Content-Type: application/json');
            $content = json_encode($content,true); // writes the whole shebang into a json packet
            
            writeJSON($server_path."content.json",$content);
            print $content;
            die();//kills the page load so you can see the endpoint urls
        }
      //writeJSON($posts_path,$file_path);

        

    }
    function getJSON($data_path){
        return file_get_contents($data_path);
    }

    function writeJSON($file_path,$data){
        //$data = file_get_contents($posts_path);
        $handle = fopen($file_path, 'w') or die('Cannot open file:  '.$file_path);
        fwrite($handle, $data);
        fclose($handle);
    }
    if(@$_GET['publish'] || @$_GET['endpoints']){
        getEndpoints();
       
    }
    
    //add_action( 'save_post', 'refreshJSON');// this will run if you save a post. Too much overhead for every save so better to trigger manually
?>