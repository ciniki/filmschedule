<?php
//
// Description
// ===========
// This method will return all the information about an event.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business the event is attached to.
// event_id:        The ID of the event to get the details for.
// 
// Returns
// -------
// <event id="419" name="Event Name" url="http://myevent.com" 
//      description="Event description" start_date="July 18, 2012" end_date="July 19, 2012"
//      date_added="2012-07-19 03:08:05" last_updated="2012-07-19 03:08:05" />
//
function ciniki_filmschedule_eventGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'event_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Event'), 
        'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
        'files'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Files'),
        'prices'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Prices'),
        'sponsors'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sponsors'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'checkAccess');
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['business_id'], 'ciniki.filmschedule.eventGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $modules = $rc['modules'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');

    //
    // Load the business intl settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
    $rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'timeFormat');
    $time_format = ciniki_users_timeFormat($ciniki, 'php');

    //
    // Load event maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'filmschedule', 'private', 'maps');
    $rc = ciniki_filmschedule_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    if( $args['event_id'] == 0 ) {
        $event = array('id'=>0,
            'name'=>'',
            'showtime'=>'',
            'showtime_date'=>'',
            'showtime_time'=>'',
            'primary_image_id'=>0,
            'synopsis'=>'',
            'description'=>'',
            'images'=>array(),
            'links'=>array(),
            );
    } else {
        $strsql = "SELECT ciniki_filmschedule_events.id, "
            . "ciniki_filmschedule_events.name, "
            . "ciniki_filmschedule_events.permalink, "
            . "ciniki_filmschedule_events.showtime, "
            . "ciniki_filmschedule_events.showtime AS showtime_date, "
            . "ciniki_filmschedule_events.showtime AS showtime_time, "
            . "ciniki_filmschedule_events.primary_image_id, "
            . "ciniki_filmschedule_events.synopsis, "
            . "ciniki_filmschedule_events.description "
            . "";
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql .= ", "
                . "ciniki_filmschedule_images.id AS img_id, "
                . "ciniki_filmschedule_images.name AS image_name, "
                . "ciniki_filmschedule_images.webflags AS image_webflags, "
                . "ciniki_filmschedule_images.image_id, "
                . "ciniki_filmschedule_images.description AS image_description, "
                . "ciniki_filmschedule_images.url AS image_url "
                . "";
        }
        $strsql .= "FROM ciniki_filmschedule_events ";
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $strsql .= "LEFT JOIN ciniki_filmschedule_images ON (ciniki_filmschedule_events.id = ciniki_filmschedule_images.event_id "
                . "AND ciniki_filmschedule_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . ") ";
        }
        $strsql .= "WHERE ciniki_filmschedule_events.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
            . "AND ciniki_filmschedule_events.id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
            . "";
        
        if( isset($args['images']) && $args['images'] == 'yes' ) {
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filmschedule', array(
                array('container'=>'events', 'fname'=>'id', 'name'=>'event',
                    'fields'=>array('id', 'name', 'showtime', 'showtime_date', 'showtime_time', 'permalink', 'primary_image_id', 
                        'synopsis', 'description'),
                    'utctotz'=>array('showtime'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                        'showtime_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
                        'showtime_time'=>array('timezone'=>$intl_timezone, 'format'=>$time_format),
                    )),
                array('container'=>'images', 'fname'=>'img_id', 'name'=>'image',
                    'fields'=>array('id'=>'img_id', 'name'=>'image_name', 'webflags'=>'image_webflags',
                        'image_id', 'description'=>'image_description', 'url'=>'image_url')),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( !isset($rc['events']) || !isset($rc['events'][0]) ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2474', 'msg'=>'Unable to find event'));
            }
            $event = $rc['events'][0]['event'];
            ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
            if( isset($event['images']) ) {
                foreach($event['images'] as $img_id => $img) {
                    if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
                        $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $img['image']['image_id'], 75);
                        if( $rc['stat'] != 'ok' ) {
                            return $rc;
                        }
                        $event['images'][$img_id]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
                    }
                }
            }
        } else {
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filmschedule', array(
                array('container'=>'events', 'fname'=>'id', 'name'=>'event',
                    'fields'=>array('id', 'name', 'showtime', 'showtime_date', 'showtime_time', 'permalink', 'primary_image_id', 
                        'synopsis', 'description'),
                    'utctotz'=>array('showtime'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                        'showtime_date'=>array('timezone'=>$intl_timezone, 'format'=>$date_format),
                        'showtime_time'=>array('timezone'=>$intl_timezone, 'format'=>$time_format),
                    )),
            ));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( !isset($rc['events']) || !isset($rc['events'][0]) ) {
                return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2475', 'msg'=>'Unable to find event'));
            }
            $event = $rc['events'][0]['event'];
        }

        //
        // Get the links for the post
        //
        if( isset($args['files']) && $args['files'] == 'yes' ) {
            $strsql = "SELECT id, name, url, description "
                . "FROM ciniki_filmschedule_links "
                . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
                . "AND ciniki_filmschedule_links.event_id = '" . ciniki_core_dbQuote($ciniki, $args['event_id']) . "' "
                . "";
            $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.blog', array(
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
        }

        //
        // Get any sponsors for this event, and that references for sponsors is enabled
        //
        if( isset($args['sponsors']) && $args['sponsors'] == 'yes' 
            && isset($ciniki['business']['modules']['ciniki.sponsors']) 
            && ($ciniki['business']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
            ) {
            ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'hooks', 'sponsorList');
            $rc = ciniki_sponsors_hooks_sponsorList($ciniki, $args['business_id'], 
                array('object'=>'ciniki.filmschedule.event', 'object_id'=>$args['event_id']));
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            if( isset($rc['sponsors']) ) {
                $event['sponsors'] = $rc['sponsors'];
            }
        }
    }

    $rsp = array('stat'=>'ok', 'event'=>$event);

    return $rsp;
}
?>
