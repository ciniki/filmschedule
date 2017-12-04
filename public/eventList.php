<?php
//
// Description
// -----------
// This method will return the list of events for a tenant.  It is restricted
// to tenant owners and sysadmins.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
// <upcoming>
//      <event id="41" name="Event name" url="http://www.ciniki.org/" description="Event description" start_date="Jul 18, 2012" end_date="Jul 20, 2012" />
// </upcoming>
// <past />
//
function ciniki_filmschedule_eventList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //  
    // Check access to tnid as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.eventList');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the tenant intl settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    $today = new DateTime('now', new DateTimeZone($intl_timezone));

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    $rsp = array('stat'=>'ok', 'upcoming'=>array(), 'past'=>array());

    //
    // Load the upcoming events
    //
    $strsql = "SELECT id, name, showtime, synopsis "
        . "FROM ciniki_filmschedule_events "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND showtime >= '" . ciniki_core_dbQuote($ciniki, $today->format('Y-m-d H:i:s')) . "' "
        . "ORDER BY ciniki_filmschedule_events.showtime ASC "
        . "";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filmschedule', array(
        array('container'=>'events', 'fname'=>'id', 'name'=>'event',
            'fields'=>array('id', 'name', 'showtime', 'synopsis'),
            'utctotz'=>array('showtime'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format))),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['events']) ) {
        $rsp['upcoming'] = $rc['events'];
    }

    //
    // Load past events
    //
    $strsql = "SELECT id, name, showtime, synopsis "
        . "FROM ciniki_filmschedule_events "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND showtime < '" . ciniki_core_dbQuote($ciniki, $today->format('Y-m-d H:i:s')) . "' "
        . "ORDER BY ciniki_filmschedule_events.showtime DESC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filmschedule', array(
        array('container'=>'events', 'fname'=>'id', 'name'=>'event',
            'fields'=>array('id', 'name', 'showtime', 'synopsis'),
            'utctotz'=>array('showtime'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format))),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['events']) ) {
        $rsp['past'] = $rc['events'];
    }


    return $rsp;
}
?>
