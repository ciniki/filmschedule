<?php
//
// Description
// -----------
// This method will add a new event for the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to add the event to.
// name:            The name of the event.
// url:             (optional) The URL for the event website.
// description:     (optional) The description for the event.
// start_date:      (optional) The date the event starts.  
// end_date:        (optional) The date the event ends, if it's longer than one day.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_filmschedule_eventAdd(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'showtime'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'datetimetoutc', 'name'=>'Showtime'), 
        'primary_image_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Image'), 
        'synopsis'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Synopsis'), 
        'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.eventAdd');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( !isset($args['permalink']) || $args['permalink'] == '' ) {  
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
    }

    //
    // Check the permalink doesn't already exist
    //
    $strsql = "SELECT id FROM ciniki_filmschedule_events "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' " 
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.filmschedule', 'event');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.filmschedule.4', 'msg'=>'You already have an event with this name, please choose another name'));
    }

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.filmschedule');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Add the event to the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    $rc = ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.filmschedule.event', $args, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
        return $rc;
    }
    $event_id = $rc['id'];

    //
    // Commit the transaction
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.filmschedule');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'filmschedule');

    return array('stat'=>'ok', 'id'=>$event_id);
}
?>
