<?php
//
// Description
// -----------
// This method will delete a event from the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the event is attached to.
// event_id:            The ID of the event to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_filmschedule_eventDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'event_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Event'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.eventDelete');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the uuid of the event to be deleted
    //
    $strsql = "SELECT uuid "
        . "FROM ciniki_filmschedule_events "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.filmschedule', 'event');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['event']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.filmschedule.5', 'msg'=>'The event does not exist'));
    }
    $event_uuid = $rc['event']['uuid'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.filmschedule');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Remove the images
    //
    $strsql = "SELECT id, uuid, image_id "
        . "FROM ciniki_filmschedule_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND event_id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.filmschedule', 'image');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        $images = $rc['rows'];
        
        foreach($images as $iid => $image) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.filmschedule.image', 
                $image['id'], $image['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
                return $rc; 
            }
        }
    }

    //
    // Remove the links
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_filmschedule_links "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND event_id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.filmschedule', 'image');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
        return $rc;
    }
    if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
        $images = $rc['rows'];
        
        foreach($links as $lid => $link) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.filmschedule.link', 
                $link['id'], $link['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
                return $rc; 
            }
        }
    }

    //
    // Remove the event
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.filmschedule.event', 
        $args['event_id'], $event_uuid, 0x04);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.filmschedule');
        return $rc;
    }

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

    return array('stat'=>'ok');
}
?>
