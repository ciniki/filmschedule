//
// The app to add/edit events event images
//
function ciniki_filmschedule_images() {
    this.webFlags = {
        '1':{'name':'Hidden'},
        };
    this.init = function() {
        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Edit Image',
            'ciniki_filmschedule_images', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.filmschedule.images.edit');
        this.edit.default_data = {};
        this.edit.data = {};
        this.edit.event_id = 0;
        this.edit.sections = {
            '_image':{'label':'Image', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
                }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text', },
                'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags,
                }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                    'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_filmschedule_images.saveImage();'},
                    'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_filmschedule_images.deleteImage();'},
                }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.filmschedule.imageHistory', 'args':{'tnid':M.curTenantID, 
                'event_image_id':this.event_image_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_filmschedule_images.edit.setFieldValue('image_id', iid, null, null);
            return true;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_filmschedule_images.saveImage();');
        this.edit.addClose('Cancel');
    };

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_filmschedule_images', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.showEdit(cb, 0, args.event_id);
        } else if( args.event_image_id != null && args.event_image_id > 0 ) {
            this.showEdit(cb, args.event_image_id);
        }
        return false;
    }

    this.showEdit = function(cb, iid, eid) {
        if( iid != null ) {
            this.edit.event_image_id = iid;
        }
        if( eid != null ) {
            this.edit.event_id = eid;
        }
        if( this.edit.event_image_id > 0 ) {
            this.edit.reset();
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            var rsp = M.api.getJSONCb('ciniki.filmschedule.imageGet', 
                {'tnid':M.curTenantID, 'event_image_id':this.edit.event_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_filmschedule_images.edit.data = rsp.image;
                    M.ciniki_filmschedule_images.edit.refresh();
                    M.ciniki_filmschedule_images.edit.show(cb);
                });
        } else {
            this.edit.reset();
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.data = {};
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveImage = function() {
        if( this.edit.event_image_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.filmschedule.imageUpdate', 
                    {'tnid':M.curTenantID, 
                    'event_image_id':this.edit.event_image_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_filmschedule_images.edit.close();
                            }
                        });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeFormData('yes');
            var rsp = M.api.postJSONFormData('ciniki.filmschedule.imageAdd', 
                {'tnid':M.curTenantID, 'event_id':this.edit.event_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_filmschedule_images.edit.close();
                        }
                    });
        }
    };

    this.deleteImage = function() {
        M.confirm('Are you sure you want to delete this image?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.filmschedule.imageDelete', {'tnid':M.curTenantID, 
                'event_image_id':M.ciniki_filmschedule_images.edit.event_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_filmschedule_images.edit.close();
                });
        });
    };
}
