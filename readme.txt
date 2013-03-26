=== OpziSlider === 

== Description == 
================================================================================
OpziSlider is a simple slider plugin based on flexslider. Slides are a custom 
post type and custom fields are permitted. Slides may be grouped so that a 
slider instance can be restricted to the slides in a requested group. A 
template tag (function) is provided to get an array of slides. This is the only
currently supported method of creating a slider. Multiple sliders are only 
partially supported. There is one set of options affecting all sliders. You can
create different sliders for different pages (templates) but multiple sliders
on a single page may be problematic. (Untested.) 


<div class="flexslider">
  <ul class="slides">

<?php 
    // Get the slides in slide-group-1. Specify the custom fields you will use. 	
    $slides = OpziSlider_slides( 'slide-group-1', array( 'xtra-1' )); 
    foreach ($slides as $slide):
?>
    <!-- <?php print_r($slide); ?> -->
    <li><?php echo $slide->img; ?>
      <div class="slide-right">
        <h1><?php echo $slide->title; ?></h1>
        <?php echo $slide->content; ?>
	<!-- Example: Using a custom field called 'xtra-1' in a span. -->
        <span><?php echo $slide->custom['xtra-1']; ?></span>
      </div>
    </li>
<?php endforeach; ?>
  </ul>
</div>

