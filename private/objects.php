<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_filmschedule_objects($ciniki) {
    
    $objects = array();
    $objects['event'] = array(
        'name'=>'Film Schedule event',
        'sync'=>'yes',
        'table'=>'ciniki_filmschedule_events',
        'fields'=>array(
            'name'=>array(),
            'showtime'=>array(),
            'permalink'=>array(),
            'primary_image_id'=>array('ref'=>'ciniki.images.image'),
            'synopsis'=>array(),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_filmschedule_history',
        );
    $objects['image'] = array(
        'name'=>'Image',
        'sync'=>'yes',
        'table'=>'ciniki_filmschedule_images',
        'fields'=>array(
            'event_id'=>array('ref'=>'ciniki.filmschedule.event'),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'description'=>array(),
            'url'=>array(),
            ),
        'history_table'=>'ciniki_filmschedule_history',
        );
    $objects['link'] = array(
        'name'=>'Link',
        'sync'=>'yes',
        'table'=>'ciniki_filmschedule_links',
        'fields'=>array(
            'event_id'=>array('ref'=>'ciniki.filmschedule.event'),
            'name'=>array(),
            'url'=>array(),
            'description'=>array(),
            ),
        'history_table'=>'ciniki_filmschedule_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>
