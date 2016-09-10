<?php
//
// Description
// -----------
// This function will return a list of user interface settings for the module.
//
// Arguments
// ---------
// ciniki:
// business_id:     The ID of the business to get events for.
//
// Returns
// -------
//
function ciniki_filmschedule_hooks_uiSettings($ciniki, $business_id, $args) {

    //
    // Any settings for the module
    //
    $settings = array();

    //
    // Setup the menu items
    //
    $menu = array();

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['business']['modules']['ciniki.filmschedule'])
        && ($args['permissions']['owners'] == 'yes'
            || $args['permissions']['employees'] == 'yes'
            || $args['permissions']['resellers'] == 'yes'
            || ($ciniki['session']['users']['perms']&0x01) == 0x01
            )
        ) {
        $menu_item = array(
            'priority'=>3100,
            'label'=>'Film Schedule', 
            'edit'=>array('app'=>'ciniki.filmschedule.main'),
            );
        $menu[] = $menu_item;
    } 

    return array('stat'=>'ok', 'settings'=>$settings, 'menu_items'=>$menu);  
}
?>
