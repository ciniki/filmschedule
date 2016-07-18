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
function ciniki_filmschedule_web_eventDetails($ciniki, $settings, $business_id, $permalink) {

    
//  print "<pre>" . print_r($ciniki, true) . "</pre>";
    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $business_id);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    $strsql = "SELECT ciniki_filmschedule_events.id, "
        . "ciniki_filmschedule_events.name, "
        . "ciniki_filmschedule_events.permalink, "
        . "ciniki_filmschedule_events.showtime, "
        . "ciniki_filmschedule_events.showtime AS start_month, "
        . "ciniki_filmschedule_events.showtime AS start_day, "
        . "ciniki_filmschedule_events.showtime AS start_year, "
        . "ciniki_filmschedule_events.showtime AS start_time, "
        . "ciniki_filmschedule_events.primary_image_id, "
        . "ciniki_filmschedule_events.synopsis, "
        . "ciniki_filmschedule_events.description "
        . "FROM ciniki_filmschedule_events "
        . "WHERE ciniki_filmschedule_events.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_filmschedule_events.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artclub', array(
        array('container'=>'events', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'image_id'=>'primary_image_id', 
            'start_day', 'start_month', 'start_year', 'start_time', 'showtime', 'synopsis', 'description'),
            'utctotz'=>array('showtime'=>array('timezone'=>$intl_timezone, 'format'=>'M D, Y'),
                'start_month'=>array('timezone'=>$intl_timezone, 'format'=>'F'),
                'start_day'=>array('timezone'=>$intl_timezone, 'format'=>'jS'),
                'start_year'=>array('timezone'=>$intl_timezone, 'format'=>'Y'),
                'start_time'=>array('timezone'=>$intl_timezone, 'format'=>'g:i a'),
            )),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['events']) || count($rc['events']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2496', 'msg'=>"I'm sorry, but we can't find the event you requested."));
    }
    $event = array_pop($rc['events']);

    //
    // Get the images for the event
    //
    $strsql = "SELECT id, image_id, name AS title, url, permalink, description, "
        . "UNIX_TIMESTAMP(ciniki_filmschedule_images.last_updated) AS last_updated "
        . "FROM ciniki_filmschedule_images "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_filmschedule_images.event_id = '" . ciniki_core_dbQuote($ciniki, $event['id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.events', array(
        array('container'=>'images', 'fname'=>'image_id', 
            'fields'=>array('id', 'image_id', 'title', 'permalink', 'description', 'url', 'last_updated')),
    ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['images']) ) {
        $event['images'] = $rc['images'];
    } else {
        $event['images'] = array();
    }

    //
    // Get the links for the event
    //
    $strsql = "SELECT id, name, url, description "
        . "FROM ciniki_filmschedule_links "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_filmschedule_links.event_id = '" . ciniki_core_dbQuote($ciniki, $event['id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.events', array(
        array('container'=>'links', 'fname'=>'id', 'name'=>'link',
            'fields'=>array('id', 'name', 'url', 'description')),
    ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['links']) ) {
        $event['links'] = $rc['links'];
    } else {
        $event['links'] = array();
    }

    //
    // Get any sponsors for this event, and that references for sponsors is enabled
    //
    if( isset($ciniki['business']['modules']['ciniki.sponsors']) 
        && ($ciniki['business']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
        $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $business_id, 
            'ciniki.events.event', $event['id']);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sponsors']) ) {
            $event['sponsors'] = $rc['sponsors'];
        }
    }

    return array('stat'=>'ok', 'event'=>$event);
}
?>
