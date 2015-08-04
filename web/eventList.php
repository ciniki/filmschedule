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
function ciniki_filmschedule_web_eventList($ciniki, $settings, $business_id, $args) {

	//
	// Load the business settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Setup the current date
	//
	$today = new DateTime('now', new DateTimeZone($intl_timezone));

	$strsql = "SELECT ciniki_filmschedule_events.id, "
		. "ciniki_filmschedule_events.name, "
		. "ciniki_filmschedule_events.showtime, "
		. "ciniki_filmschedule_events.showtime AS start_month, "
		. "ciniki_filmschedule_events.showtime AS start_day, "
		. "ciniki_filmschedule_events.showtime AS start_year, "
		. "ciniki_filmschedule_events.showtime AS start_time, "
		. "ciniki_filmschedule_events.permalink, "
		. "ciniki_filmschedule_events.primary_image_id, "
		. "ciniki_filmschedule_events.synopsis, "
		. "'yes' AS isdetails "
		. "FROM ciniki_filmschedule_events "
		. "WHERE ciniki_filmschedule_events.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	if( isset($args['type']) && $args['type'] == 'past' ) {
		$strsql .= "AND ciniki_filmschedule_events.showtime < '" . ciniki_core_dbQuote($ciniki, $today->format('Y-m-d H:i')) . "' "
			. "ORDER BY ciniki_filmschedule_events.showtime DESC ";	
	} else {
		$strsql .= "AND ciniki_filmschedule_events.showtime > '" . ciniki_core_dbQuote($ciniki, $today->format('Y-m-d H:i')) . "' "
			. "ORDER BY ciniki_filmschedule_events.showtime ASC ";	
	}
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 && is_int($args['limit']) ) {
		$strsql .= "LIMIT " . $args['limit'] . " ";
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.filmschedule', array(
		array('container'=>'events', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'permalink', 'image_id'=>'primary_image_id', 'isdetails', 'synopsis',
				'showtime', 'start_month', 'start_day', 'start_year', 'start_time'),
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
	if( !isset($rc['events']) ) {
		return array('stat'=>'ok', 'events'=>array());
	}
	return array('stat'=>'ok', 'events'=>$rc['events']);
}
?>
