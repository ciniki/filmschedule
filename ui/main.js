//
// This app will handle the listing, additions and deletions of filmschedule.  These are associated business.
//
function ciniki_filmschedule_main() {
	//
	// Panels
	//
	this.regFlags = {
		'1':{'name':'Track Registrations'},
		'2':{'name':'Online Registrations'},
		};
	this.init = function() {
		//
		// events panel
		//
		this.menu = new M.panel('Events',
			'ciniki_filmschedule_main', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.filmschedule.main.menu');
        this.menu.sections = {
			'upcoming':{'label':'Upcoming Films', 'type':'simplegrid', 'num_cols':2,
				'headerValues':null,
				'cellClasses':['multiline center nobreak', 'multiline'],
				'noData':'No upcoming films',
				'addTxt':'Add Event',
				'addFn':'M.ciniki_filmschedule_main.showEdit(\'M.ciniki_filmschedule_main.showMenu();\',0);',
				},
			'past':{'label':'Past Films', 'type':'simplegrid', 'num_cols':2,
				'headerValues':null,
				'cellClasses':['multiline center nobreak', 'multiline'],
				'noData':'No past films'
				},
			};
		this.menu.sectionData = function(s) { return this.data[s]; }
		this.menu.noData = function(s) { return this.sections[s].noData; }
		this.menu.cellValue = function(s, i, j, d) {
			switch (j) {
				case 0: return d.event.showtime;
				case 1: return d.event.name;
			}
		};
		this.menu.rowFn = function(s, i, d) {
			return 'M.ciniki_filmschedule_main.showEvent(\'M.ciniki_filmschedule_main.showMenu();\',\'' + d.event.id + '\');';
		};
		this.menu.addButton('add', 'Add', 'M.ciniki_filmschedule_main.showEdit(\'M.ciniki_filmschedule_main.showMenu();\',0);');
		this.menu.addClose('Back');

		//
		// The event panel 
		//
		this.event = new M.panel('Film',
			'ciniki_filmschedule_main', 'event',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.filmschedule.main.event');
		this.event.data = {};
		this.event.event_id = 0;
		this.event.sections = {
			'_image':{'label':'', 'aside':'yes', 'type':'imageform', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
				}},
			'info':{'label':'', 'aside':'yes', 'list':{
				'name':{'label':'Name'},
				'showtime':{'label':'Showtime'},
				}},
			'synopsis':{'label':'Synopsis', 'type':'htmlcontent'},
			'description':{'label':'Description', 'type':'htmlcontent'},
			'links':{'label':'Links', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':['multiline'],
				'noData':'No event links',
				'addTxt':'Add Link',
				'addFn':'M.startApp(\'ciniki.filmschedule.links\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'event_id\':M.ciniki_filmschedule_main.event.event_id,\'add\':\'yes\'});',
				},
			'images':{'label':'Gallery', 'type':'simplethumbs'},
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Additional Image',
				'addFn':'M.startApp(\'ciniki.filmschedule.images\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'event_id\':M.ciniki_filmschedule_main.event.event_id,\'add\':\'yes\'});',
				},
			'sponsors':{'label':'Sponsors', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Manage Sponsors',
				'addFn':'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'object\':\'ciniki.filmschedule.event\',\'object_id\':M.ciniki_filmschedule_main.event.event_id});',
				},
			'_buttons':{'label':'', 'buttons':{
				'edit':{'label':'Edit', 'fn':'M.ciniki_filmschedule_main.showEdit(\'M.ciniki_filmschedule_main.showEvent();\',M.ciniki_filmschedule_main.event.event_id);'},
				}},
		};
		this.event.addDropImage = function(iid) {
			var rsp = M.api.getJSON('ciniki.filmschedule.imageAdd',
				{'business_id':M.curBusinessID, 'image_id':iid, 'event_id':M.ciniki_filmschedule_main.event.event_id});
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			return true;
		};
		this.event.addDropImageRefresh = function() {
			if( M.ciniki_filmschedule_main.event.event_id > 0 ) {
				var rsp = M.api.getJSONCb('ciniki.filmschedule.eventGet', {'business_id':M.curBusinessID, 
					'event_id':M.ciniki_filmschedule_main.event.event_id, 'images':'yes'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_filmschedule_main.event.data.images = rsp.event.images;
						M.ciniki_filmschedule_main.event.refreshSection('images');
					});
			}
		};
		this.event.sectionData = function(s) {
			if( s == 'synopsis' || s == 'description' ) { return this.data[s].replace(/\n/g, '<br/>'); }
			if( s == 'info' ) { return this.sections[s].list; }
			return this.data[s];
		};
		this.event.listLabel = function(s, i, d) { return d.label; };
		this.event.listValue = function(s, i, d) {
			return this.data[i];
		};
		this.event.listFn = function(s, i, d) {
			return null;
		};
		this.event.fieldValue = function(s, i, d) {
			return this.data[i];
		};
		this.event.cellValue = function(s, i, j, d) {
			if( s == 'links' && j == 0 ) {
				return '<span class="maintext">' + d.link.name + '</span><span class="subtext">' + d.link.url + '</span>';
			}
			if( s == 'sponsors' && j == 0 ) { 
				return '<span class="maintext">' + d.sponsor.title + '</span>';
			}
		};
		this.event.rowFn = function(s, i, d) {
			if( s == 'links' ) {
				return 'M.startApp(\'ciniki.filmschedule.links\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'link_id\':\'' + d.link.id + '\'});';
			}
			if( s == 'sponsors' ) {
				return 'M.startApp(\'ciniki.sponsors.ref\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'ref_id\':\'' + d.sponsor.ref_id + '\'});';
			}
		};
		this.event.thumbFn = function(s, i, d) {
			return 'M.startApp(\'ciniki.filmschedule.images\',null,\'M.ciniki_filmschedule_main.showEvent();\',\'mc\',{\'event_image_id\':\'' + d.image.id + '\'});';
		};
		this.event.addButton('edit', 'Edit', 'M.ciniki_filmschedule_main.showEdit(\'M.ciniki_filmschedule_main.showEvent();\',M.ciniki_filmschedule_main.event.event_id);');
		this.event.addLeftButton('website', 'Preview', 'M.showWebsite(\'/filmschedule/\'+M.ciniki_filmschedule_main.event.data.permalink);');
		this.event.addClose('Back');

		//
		// The panel for a site's menu
		//
		this.edit = new M.panel('Event',
			'ciniki_filmschedule_main', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.filmschedule.main.edit');
		this.edit.data = null;
		this.edit.event_id = 0;
        this.edit.sections = { 
			'_image':{'label':'Image', 'type':'imageform', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
				}},
            'general':{'label':'General', 'aside':'yes', 'fields':{
				'name':{'label':'Name', 'hint':'Events name', 'type':'text', },
				'showtime_date':{'label':'Date', 'type':'date', },
				'showtime_time':{'label':'Time', 'type':'text', 'size':'small'},
				}}, 
			'_synopsis':{'label':'Synopsis', 'fields':{
				'synopsis':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'small', 'type':'textarea'},
				}},
			'_description':{'label':'Description', 'fields':{
				'description':{'label':'', 'hidelabel':'yes', 'hint':'', 'size':'large', 'type':'textarea'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_filmschedule_main.saveEvent();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_filmschedule_main.removeEvent();'},
				}},
            };  
		this.edit.fieldValue = function(s, i, d) { return this.data[i]; }
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.filmschedule.eventHistory', 'args':{'business_id':M.curBusinessID, 
				'event_id':this.event_id, 'field':i}};
		}
		this.edit.addDropImage = function(iid) {
			M.ciniki_filmschedule_main.edit.setFieldValue('primary_image_id', iid, null, null);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0, null, null);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_filmschedule_main.saveEvent();');
		this.edit.addClose('Cancel');
	}

	//
	// Arguments:
	// aG - The arguments to be parsed into args
	//
	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create the app container if it doesn't exist, and clear it out
		// if it does exist.
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_filmschedule_main', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		} 

		if( M.curBusiness.modules['ciniki.sponsors'] != null 
			&& (M.curBusiness.modules['ciniki.sponsors'].flags&0x02) ) {
			this.event.sections.sponsors.visible = 'yes';
		} else {
			this.event.sections.sponsors.visible = 'no';
		}

		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		this.menu.data = {};
		if( this.menu.rightbuttons.edit != null ) { delete(this.menu.rightbuttons.edit); }
		M.api.getJSONCb('ciniki.filmschedule.eventList', {'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_filmschedule_main.menu;
				p.data = rsp;
				p.refresh();
				p.show(cb);
			});
	};

	this.showEvent = function(cb, eid) {
		this.event.reset();
		if( eid != null ) { this.event.event_id = eid; }
		var rsp = M.api.getJSONCb('ciniki.filmschedule.eventGet', {'business_id':M.curBusinessID, 
			'event_id':this.event.event_id, 'images':'yes', 'sponsors':'yes', 'links':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_filmschedule_main.event;
				p.data = rsp.event;
				p.refresh();
				p.show(cb);
			});
	};

	this.showEdit = function(cb, eid) {
		this.edit.reset();
		if( eid != null ) { this.edit.event_id = eid; }

		this.edit.sections._buttons.buttons.delete.visible = (this.edit.event_id>0?'yes':'no');
		this.edit.reset();
		this.edit.sections._buttons.buttons.delete.visible = 'yes';
		M.api.getJSONCb('ciniki.filmschedule.eventGet', {'business_id':M.curBusinessID, 
			'event_id':this.edit.event_id, 'webcollections':'yes', 'categories':'yes', 'objects':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_filmschedule_main.edit;
				p.data = rsp.event;
				p.refresh();
				p.show(cb);
			});
	};

	this.saveEvent = function() {
		var showtime = this.edit.formFieldValue(this.edit.sections.general.fields.showtime_date, 'showtime_date')
			+ ' ' + this.edit.formFieldValue(this.edit.sections.general.fields.showtime_time, 'showtime_time');
		if( this.edit.event_id > 0 ) {
			var c = this.edit.serializeForm('no');
			if( this.edit.data.showtime != showtime ) {
				c += '&showtime=' + encodeURIComponent(showtime);
			}
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.filmschedule.eventUpdate', 
					{'business_id':M.curBusinessID, 'event_id':M.ciniki_filmschedule_main.edit.event_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
					M.ciniki_filmschedule_main.edit.close();
					});
			} else {
				this.edit.close();
			}
		} else {
			var c = this.edit.serializeForm('yes');
				c += '&showtime=' + encodeURIComponent(showtime);
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.filmschedule.eventAdd', 
					{'business_id':M.curBusinessID}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						if( rsp.id > 0 ) {
							var cb = M.ciniki_filmschedule_main.edit.cb;
							M.ciniki_filmschedule_main.edit.close();
							M.ciniki_filmschedule_main.showEvent(cb,rsp.id);
						} else {
							M.ciniki_filmschedule_main.edit.close();
						}
					});
			} else {
				this.edit.close();
			}
		}
	};

	this.removeEvent = function() {
		if( confirm("Are you sure you want to remove '" + this.event.data.name + "' as an event ?") ) {
			var rsp = M.api.getJSONCb('ciniki.filmschedule.eventDelete', 
				{'business_id':M.curBusinessID, 'event_id':M.ciniki_filmschedule_main.event.event_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_filmschedule_main.event.close();
				});
		}
	}
};
