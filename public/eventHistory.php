<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an event. 
// This method is typically used by the UI to display a list of changes that have occured 
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// event_id:            The ID of the event to get the history for.
// field:               The field to get the history for. This can be any of the elements 
//                      returned by the ciniki.filmschedule.get method.
//
// Returns
// -------
// <history>
// <action user_id="2" date="May 12, 2012 10:54 PM" value="Event Name" age="2 months" user_display_name="Andrew" />
// ...
// </history>
//
function ciniki_filmschedule_eventHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'event_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Event'), 
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.eventHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    if( $args['field'] == 'showtime' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.filmschedule', 'ciniki_filmschedule_history', $args['tnid'], 'ciniki_filmschedule_events', $args['event_id'], $args['field'],'utcdatetime');
    }
    elseif( $args['field'] == 'showtime_date' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.filmschedule', 'ciniki_filmschedule_history', $args['tnid'], 'ciniki_filmschedule_events', $args['event_id'], 'showtime','utcdate');
    }
    elseif( $args['field'] == 'showtime_time' ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
        return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.filmschedule', 'ciniki_filmschedule_history', $args['tnid'], 'ciniki_filmschedule_events', $args['event_id'], 'showtime','utctime');
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.filmschedule', 'ciniki_filmschedule_history', $args['tnid'], 'ciniki_filmschedule_events', $args['event_id'], $args['field']);
}
?>
