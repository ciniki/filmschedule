<?php
//
// Description
// -----------
// This method returns the information about a link attached to an event.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the link is attached to.
// link_id:             The ID of the link to get.
//
// Returns
// -------
//
function ciniki_filmschedule_linkGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'link_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Link'),
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
    $rc = ciniki_filmschedule_checkAccess($ciniki, $args['tnid'], 'ciniki.filmschedule.linkGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_filmschedule_links.id, "
        . "ciniki_filmschedule_links.name, "
        . "ciniki_filmschedule_links.url, "
        . "ciniki_filmschedule_links.description "
        . "FROM ciniki_filmschedule_links "
        . "WHERE ciniki_filmschedule_links.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ciniki_filmschedule_links.id = '" . ciniki_core_dbQuote($ciniki, $args['link_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filmschedule', array(
        array('container'=>'links', 'fname'=>'id', 'name'=>'link',
            'fields'=>array('id', 'name', 'url', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['links']) ) {
        return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.filmschedule.20', 'msg'=>'Unable to find link'));
    }
    $link = $rc['links'][0]['link'];
    
    return array('stat'=>'ok', 'link'=>$link);
}
?>
