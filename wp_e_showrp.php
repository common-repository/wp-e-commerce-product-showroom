<?php
/*
Plugin Name: WP e-Commerce Product Showroom
Plugin URI: http://www.wpworking.com/
Description: A categorized product list multi widget/shortcodes.Works with the WP e-commerce(tested on 3.8.6 version)
Based on Sample Hello World Plugin 2 (http://lonewolf-online.net/) by Tim Trott(http://lonewolf-online.net/)
and WP e-Commerce Featured Product by Zorgbargle | Phenomenoodle http://www.phenomenoodle.com
Version: 5.0.0
Author: Alvaro Neto
Author URI: http://wpworks.wordpress.com/
License: GPL2
*/

/*  Copyright 2011  Alvaro Neto  (email : alvaron8@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
add_shortcode('wpeshow','wpeshow_short');
//
function wpeshow_short($atts, $content = null){
   extract(shortcode_atts(array(
        'prodnr' => '9',
        'tbwid' => '100',
        'catg' => '',
        'rndw' => false
    ), $atts));
    wpeshowrp($prodnr,$tbwid,$catg,$rndw);
}
 
// display a list of products with custom size thumbnails
function wpeshowrp($prodnr,$tbwid,$catg,$rndw){
	if($prodnr=='' || $prodnr <= 0){
		$prodnr = 9;
	}
	if($tbwid=='' || $tbwid <= 0){
		$tbwid = 100;
	}
	if($rndw =='false'){       
        $rndw = false;
        //echo "teste:".$par;
    }
	if($rndw =='true'){       
        $rndw = true;
        //echo "teste:".$par;
    }
	$conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    mysql_select_db(DB_NAME) or die ("Could not select database, check you wp-config file" . mysql_error());
    // if you are note sure about the value of $wp_table_prefix, perform a search for this
    // text on your site, the usual value is "wp_" or something
	///////////////////////////////////
	// if you are note sure about the value of $wp_table_prefix, perform a search for this
    // text on your site, the usual value is "wp_" or something
    $sql = "select pt.ID, pt.post_title from wp_posts As pt  ";
    $sql .= "LEFT JOIN wp_postmeta As mt ON pt.ID=mt.post_id  ";
	/* just in case a category is choosen */
	if($catg != ""):	
	$sql .= "LEFT JOIN wp_term_relationships As tr ON pt.ID=tr.object_id ";
	endif;
	if($catg != ""):	
	$sql .= "LEFT JOIN  wp_terms As tt ON tr.term_taxonomy_id = tt.term_id ";
	endif;
    $sql .= "WHERE pt.post_type = 'wpsc-product' ";
    $sql .= "AND pt.post_status = 'publish' ";
	if($catg != ""):	
	$sql .= "and tt.term_id =". $catg;
	endif;
    $sql .= " GROUP BY mt.post_id ";
	if($rndw):
	$sql .= "ORDER BY rand() ";
	else:
	$sql .= "ORDER BY pt.post_title ";
	endif;
	$sql .= "LIMIT ".$prodnr."";
	//echo $sql;
    $qry = mysql_query($sql);
    $nrt = mysql_num_rows($qry);
    while($rs = mysql_fetch_array($qry)):
		//
		$sqlp = "select meta_value from wp_postmeta as wm, wp_posts wp ";
		$sqlp .= "where wm.meta_key='_wpsc_price' and wm.post_id=".$rs[0];
		$sqlp .= " GROUP BY wm.post_id";
		$qryp = mysql_query($sqlp);
		while($rsp = mysql_fetch_array($qryp)):
			$prc = $rsp[0];
		endwhile;
		//
		$sqli = "select meta_value from wp_postmeta as wm, wp_posts wp ";
		$sqli .= "where wm.meta_key='_thumbnail_id' and wm.post_id=".$rs[0];
		$sqli .= " GROUP BY wm.post_id";
		$qryi = mysql_query($sqli);
		while($rsi = mysql_fetch_array($qryi)):
			//
			$sqlt = "select guid from wp_posts where ID=".$rsi[0];
			$qryt = mysql_query($sqlt);
			while($rst = mysql_fetch_array($qryt)):
				$pht = $rst[0];
			endwhile;
		endwhile;
	

	   $htmlcod .= "<div class='showrpdiv'>\n";
	   $htmlcod .= "<ul>\n";
	   $htmlcod .= "<li><a href=".wpsc_product_url($rs[0]).">".$rs[1]."</a><br>";
	   $htmlcod .= "<a href='".wpsc_product_url($rs[0])."'>";
	   $htmlcod .= "<img src='".$pht."' style='width:".$tbwid."px'></a>\n";
	   $htmlcod .= "</li>\n";
	   $htmlcod .= "<li>".$prc."</li>\n";                   
	   $htmlcod .= "</ul>\n";
	   $htmlcod .= "</div>\n";

	endwhile;


	echo $htmlcod;
}

 
function widget_wpeshowrp($args) {
  extract($args);
 
  $options = get_option("widget_wpeshowrp");
  if (!is_array( $options ))
{
$options = array(
	  'prodnr' => '',
	  'tbwid' => '',
	  'catg' => '',
	  'rndw' => ''
      );
  }
 
 /*echo $before_widget;
    echo $before_title;
      echo $options['title'];
    echo $after_title;   
  echo $after_widget;*/
  //Our Widget Content
  //wpeshowrp
    wpeshowrp($options['prodnr'],$options['tbwid'],$options['catg'],$options['rndw']);
}
 
function wpeshowrp_control()
{
  $options = get_option("widget_wpeshowrp");
  if (!is_array( $options ))
{
$options = array(
	  'prodnr' => '',
	  'tbwid' => '',
	  'catg' => '',
	  'rndw' => ''
      );
  }
 
  if ($_POST['wpeshowrp-Submit'])
  {
	$options['prodnr'] = htmlspecialchars($_POST['wpeshowrp-WidgetProdNr']);
	$options['tbwid'] = htmlspecialchars($_POST['wpeshowrp-WidgetTbWid']);
	$options['catg'] = htmlspecialchars($_POST['wpeshowrp-WidgetCatg']);
	$options['rndw'] = htmlspecialchars($_POST['wpeshowrp-WidgetRndw']);
    update_option("widget_wpeshowrp", $options);
  }
  
  //
	if($options['rndw']){
		$rchk = "checked";
	}
	else{
		$rchk = "";
	}
	
?>
  <p>    
    <label for="wpeshowrp-WidgetPostNr">Number of Products(default=9): </label><br />
    <input type="text" id="wpeshowrp-WidgetProdNr" name="wpeshowrp-WidgetProdNr" value="<?php echo $options['prodnr'];?>" size="4"/>
    <br />
    <label for="wpeshowrp-WidgetTbWid">Product Image Width(default=100): </label><br />
    <input type="text" id="wpeshowrp-WidgetTbWid" name="wpeshowrp-WidgetTbWid" value="<?php echo $options['tbwid'];?>" size="4"/>
    <br />
    <label for="wpeshowrp-WidgetCatg">Product Category: </label><br />    
    <?
    $conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    mysql_select_db(DB_NAME) or die ("Could not select database, check you wp-config file" . mysql_error());
    
    $sqlt = "select wp_terms.term_id, wp_terms.name from wp_terms ";
	$sqlt .= "LEFT JOIN wp_term_taxonomy ";
	$sqlt .= "on wp_terms.term_id = wp_term_taxonomy.term_id ";
	$sqlt .= "and wp_term_taxonomy.parent=0 ";
	$sqlt .= "where wp_term_taxonomy.taxonomy='wpsc_product_category' ";
	$sqlt .= "order by wp_term_taxonomy.parent, wp_terms.name ";
   
    $qryt = mysql_query($sqlt);
    $nrn = mysql_num_rows($qryt);
	if($nrn > 0 ):
	?>
    <select id="wpeshowrp-WidgetCatg" name="wpeshowrp-WidgetCatg">
    <option value="">Select one</option>
    <?
    while($rst = mysql_fetch_array($qryt)):
		if($options['catg']==$rst[0]):
			$selths = "selected";
		else:
			$selths = "";
		endif;
	?>
  	<option value="<?=$rst[0];?>" <?=$selths;?>><?=$rst[1];?></option>
    <?
		$sqlt2 = "select wp_terms.term_id, wp_terms.name from wp_terms ";
		$sqlt2 .= "LEFT JOIN wp_term_taxonomy ";
		$sqlt2 .= "on wp_terms.term_id = wp_term_taxonomy.term_id ";
		$sqlt2 .= "and wp_term_taxonomy.parent=".$rst[0];
		$sqlt2 .= " where wp_term_taxonomy.taxonomy='wpsc_product_category' ";
		$sqlt2 .= "order by wp_term_taxonomy.parent, wp_terms.name ";
		echo $sqlt2;
	   
		$qryt2 = mysql_query($sqlt2);
		$nrn2 = mysql_num_rows($qryt2);
		if($nrn2 > 0 ):
			 while($rst2 = mysql_fetch_array($qryt2)):
			 if($options['catg']==$rst2[0]):
				$selths = "selected";
			else:
				$selths = "";
			endif;
			 ?>
            <option value="<?=$rst2[0];?>" <?=$selths;?>>- <?=$rst2[1];?></option>
            <?
			 endwhile;
		endif;
	endwhile;
	?>
     </select>
    <?
	else:
	?>
    No product category found.<br /><br />
    <?
	endif;
	?>
    <br /><br />
    <label for="wpeshowrp-WidgetRndw">Random Order(if unchecked will order by product title): </label><br />
    <input type="checkbox" id="wpeshowrp-WidgetRndw" name="wpeshowrp-WidgetRndw"  <?=$rchk?> />
    <br /><br />  
    <label>The result list will be a div, that uses the css class 'showrpdiv', so you can play with the div properties, and the inside elements.</label><br />
    <input type="hidden" id="wpeshowrp-Submit" name="wpeshowrp-Submit" value="1" />
  </p>
<?php
}
 
function wpeshowrp_init()
{
  register_sidebar_widget(__('WPe-CommerceProductShowroom'), 'widget_wpeshowrp');  
  register_widget_control(   'WPe-CommerceProductShowroom', 'wpeshowrp_control', 350, 350 );
}
//add_action("plugins_loaded", "wpeshowrp_init");
add_action('widgets_init', wpeshowrp_register_widgets);
function wpeshowrp_register_widgets(){
	// curl need to be installed
	register_widget('WPS_Widget');
}
///////////////////////////////////

class WPS_Widget extends WP_Widget {

	
	function WPS_Widget() {

		/* Widget settings. */

		$widget_ops = array( 'classname' => 'wps', 'description' => 'This is the WP e-Commerce Product Showroom Plugin Widget.' );



		/* Widget control settings. */

		$control_ops = array( 'width' => 350, 'height' => 350, 'id_base' => 'wps-widget' );



		/* Create the widget. */

		$this->WP_Widget( 'wps-widget', 'WPS Widget', $widget_ops, $control_ops );

	}

	function widget( $args, $instance ) {

		extract( $args );



		//$instance = get_option("widget_getPostListThumbs");

		  if (!is_array( $options ))

		{

		$options = array(

			  'prodnr' => '',
			  'tbwid' => '',
			  'catg' => '',
			  'rndw' => ''

			  );

		  }

		 

		 /*echo $before_widget;

			echo $before_title;

			  echo $options['title'];

			echo $after_title;  

		  echo $after_widget;*/

		  //Our Widget Content

			 wpeshowrp($instance['prodnr'],$instance['tbwid'],$instance['catg'],$instance['rndw']);

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;



		/* Strip tags (if needed) and update the widget settings. */

		$instance['prodnr'] = $new_instance['prodnr'];

		$instance['tbwid'] = $new_instance['tbwid'];

		$instance['catg'] = $new_instance['catg'];

		$instance['rndw'] = $new_instance['rndw'];

		return $instance;

	}

	function form( $instance ) {



		/* Set up some default widget settings. */

		$defaults = array( 'prodnr' => '9',
        'tbwid' => '100',
        'catg' => '',
        'rndw' => false );

		$instance = wp_parse_args( (array) $instance, $defaults );

	             //          

                if($instance['rndw']){
					$rchk = "checked";
				}
				else{
					$rchk = "";
				}
				


            ?>

            <p>    
                <label for="<?php echo $this->get_field_id( 'prodnr' ); ?>">Number of Products(default=9): </label><br />
                <input type="text" id="<?php echo $this->get_field_id( 'prodnr' ); ?>" name="<?php echo $this->get_field_name( 'prodnr' ); ?>" value="<?php echo $instance['prodnr'];?>" size="4"/>
                <br />
                <label for="<?php echo $this->get_field_id( 'tbwid' ); ?>">Product Image Width(default=100): </label><br />
                <input type="text" id="<?php echo $this->get_field_id( 'tbwid' ); ?>" name="<?php echo $this->get_field_name( 'tbwid' ); ?>" value="<?php echo $instance['tbwid'];?>" size="4"/>
                <br />
                <label for="<?php echo $this->get_field_id( 'catg' ); ?>">Product Category: </label><br />    
                <?
                $conn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
                mysql_select_db(DB_NAME) or die ("Could not select database, check you wp-config file" . mysql_error());
                
                $sqlt = "select wp_terms.term_id, wp_terms.name from wp_terms ";
                $sqlt .= "LEFT JOIN wp_term_taxonomy ";
                $sqlt .= "on wp_terms.term_id = wp_term_taxonomy.term_id ";
                $sqlt .= "and wp_term_taxonomy.parent=0 ";
                $sqlt .= "where wp_term_taxonomy.taxonomy='wpsc_product_category' ";
                $sqlt .= "order by wp_term_taxonomy.parent, wp_terms.name ";
               
                $qryt = mysql_query($sqlt);
                $nrn = mysql_num_rows($qryt);
                if($nrn > 0 ):
                ?>
                <select id="<?php echo $this->get_field_id( 'catg' ); ?>" name="<?php echo $this->get_field_name( 'catg' ); ?>">
                <option value="">Select one</option>
                <?
                while($rst = mysql_fetch_array($qryt)):
                    if($instance['catg']==$rst[0]):
                        $selths = "selected";
                    else:
                        $selths = "";
                    endif;
                ?>
                <option value="<?=$rst[0];?>" <?=$selths;?>><?=$rst[1];?></option>
                <?
                    $sqlt2 = "select wp_terms.term_id, wp_terms.name from wp_terms ";
                    $sqlt2 .= "LEFT JOIN wp_term_taxonomy ";
                    $sqlt2 .= "on wp_terms.term_id = wp_term_taxonomy.term_id ";
                    $sqlt2 .= "and wp_term_taxonomy.parent=".$rst[0];
                    $sqlt2 .= " where wp_term_taxonomy.taxonomy='wpsc_product_category' ";
                    $sqlt2 .= "order by wp_term_taxonomy.parent, wp_terms.name ";
                    echo $sqlt2;
                   
                    $qryt2 = mysql_query($sqlt2);
                    $nrn2 = mysql_num_rows($qryt2);
                    if($nrn2 > 0 ):
                         while($rst2 = mysql_fetch_array($qryt2)):
                         if($instance['catg']==$rst2[0]):
                            $selths = "selected";
                        else:
                            $selths = "";
                        endif;
                         ?>
                        <option value="<?=$rst2[0];?>" <?=$selths;?>>- <?=$rst2[1];?></option>
                        <?
                         endwhile;
                    endif;
                endwhile;
                ?>
                 </select>
                <?
                else:
                ?>
                No product category found.<br /><br />
                <?
                endif;
                ?>
                <br /><br />
                <label for="<?php echo $this->get_field_id( 'rndw' ); ?>">Random Order(if unchecked will order by product title): </label><br />
                <input type="checkbox" id="<?php echo $this->get_field_id( 'rndw' ); ?>" name="<?php echo $this->get_field_name( 'rndw' ); ?>"  <?=$rchk?> />
                <br /><br />  
                <label>The result list will be a div, that uses the css class 'showrpdiv', so you can play with the div properties, and the inside elements.</label><br />
                <input type="hidden" id="wpeshowrp-Submit" name="wpeshowrp-Submit" value="1" />
              </p>

        <?php



    }

}
?>