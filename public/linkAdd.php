<?php
//
// Description
// -----------
// This method will add a new event link to an event.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_filmschedule_linkAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'event_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Post'),
        'name'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Name'), 
        'url'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'URL'),
        'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.linkAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Check the url does not already exist for this events 
    //
    $strsql = "SELECT id "
        . "FROM ciniki_filmschedule_links "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND url = '" . ciniki_core_dbQuote($ciniki, $args['url']) . "' "
        . "AND event_id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.filmschedule', 'link');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['link']) || (isset($rc['rows']) && count($rc['rows']) > 0) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.filmschedule.18', 'msg'=>'You already have a event link with that url, please choose another'));
    }

    //
    // Add the event link
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.filmschedule.link', $args, 0x07);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $link_id = $rc['id'];

    return array('stat'=>'ok', 'id'=>$link_id);
}
?>
