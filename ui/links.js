//
function ciniki_filmschedule_links() {
    //
    // Panels
    //
    this.init = function() {
        //
        // The panel to edit an existing link
        //
        this.edit = new M.panel('Link',
            'ciniki_filmschedule_links', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.event.links.edit');
        this.edit.data = {};
        this.edit.link_id = 0;
        this.edit.sections = {
            'link':{'label':'Link', 'fields':{
                'name':{'label':'Name', 'hint':'', 'type':'text', },
                'url':{'label':'URL', 'hint':'', 'type':'text', },
                }},
            '_description':{'label':'Additional Information', 'fields':{
                'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'type':'textarea'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_filmschedule_links.saveLink();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_filmschedule_links.deleteLink();'},
                }},
            };
        this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.filmschedule.linkHistory', 'args':{'tnid':M.curTenantID, 
                'link_id':this.link_id, 'field':i}};
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_filmschedule_links.saveLink();');
        this.edit.addClose('cancel');
    };

    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_filmschedule_links', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 

        if( args.link_id != null && args.link_id > 0 ) {
            // Edit an existing link
            this.showEdit(cb, 0, args.link_id);
        } else if( args.event_id != null && args.event_id > 0 ) {
            // Add a new link for a event
            this.showEdit(cb, args.event_id, 0);
        }
    };

    this.showEdit = function(cb, pid, lid) {
        if( pid != null ) { this.edit.event_id = pid; }
        if( lid != null ) { this.edit.link_id = lid; }
        if( this.edit.link_id > 0 ) {
            this.edit.reset();
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            var rsp = M.api.getJSONCb('ciniki.filmschedule.linkGet', 
                {'tnid':M.curTenantID, 'link_id':this.edit.link_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_filmschedule_links.edit;
                    p.data = rsp.link;
                    p.refresh();
                    p.show(cb);
                });
        } else {
            this.edit.reset();
            this.edit.data = {};
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveLink = function() {
        if( this.edit.link_id > 0 ) {
            var c = this.edit.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.filmschedule.linkUpdate', 
                    {'tnid':M.curTenantID, 'link_id':this.edit.link_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_filmschedule_links.edit.close();
                    });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeForm('yes');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.filmschedule.linkAdd', 
                    {'tnid':M.curTenantID, 'event_id':this.edit.event_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } 
                        M.ciniki_filmschedule_links.edit.close();
                    });
            } else {
                this.edit.close();
            }
        }
    };

    this.deleteLink = function() {
        if( confirm("Are you sure you want to remove this link?") ) {
            var rsp = M.api.getJSONCb('ciniki.filmschedule.linkDelete', 
                {'tnid':M.curTenantID, 'link_id':this.edit.link_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_filmschedule_links.edit.close();
                });
        }   
    };
}
