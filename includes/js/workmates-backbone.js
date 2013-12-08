var wm = wm || {};

/**
 * WorkMates Backbone
 */
( function( $ ) {
	var media;

	wm.media = media = {};
	wml10n = wp.media.view.l10n;

	_.extend( media, { model: {}, view: {}, controller: {}, frames: {} } );

	WorkMatesUser = wm.media.model.WorkMatesUser = Backbone.Model.extend( {
		defaults : {
            id:0,
            avatar:'',
            name:'',
        },
	} );

	WorkMatesUsers = wm.media.model.WorkMatesUsers = Backbone.Collection.extend( {
		model: WorkMatesUser,

		initialize : function() {
            this.options = { current_page: 1, total_page:0 };
        },

        sync: function( method, model, options ) {

            if( 'read' === method ) {
                options = options || {};
                options.context = this;
                options.data = _.extend( options.data || {}, {
                    action: 'workmates_get_users',
                    query: { 
                    	group_id: wp.media.view.settings.group,
                    	page:this.options.current_page,
                    	search_terms: media.frame().state().get( 'search' ),
                    },
                    _wpnonce: wp.media.view.settings.nonce.workmates
                });
                
                return wp.ajax.send( options );
            }
        },

        parse: function( resp, xhr ) {
            
            if ( ! _.isArray( resp.items ) )
                resp.items = [resp.items];

            _.each( resp.items, function( value, index ) {
                if ( _.isNull( value ) )
                    return;
                
                resp.items[index].id = value.id;
                resp.items[index].avatar = value.avatar;
                resp.items[index].name = value.name;
            });

            if( ! _.isUndefined( resp.meta ) ){
                 this.options.current_page = resp.meta.current_page;
                 this.options.total_page = resp.meta.total_page;
            }
            
            return resp.items;
            
        },
	} );

	media.view.WorkMatesUser = wp.media.View.extend( {
		className: 'workmates-users attachment',
		tagName: 'li',
		template: wp.media.template( 'workmates' ),

		render:function() {
			this.$el.html( this.template( this.model.toJSON() ) );
            return this;
		}
	} );

	media.view.WorkMatesUsers = wp.media.View.extend( {
		className: 'list-workmates-users',
		tagName: 'ul',

		events: {
			'click .attachment-preview' : 'toggleSelectionHandler',
			'click .workmates-users .check'   : 'removeSelectionHandler',
		},

		initialize:function() {
			if( this.views.length )
				this.views.remove();

			this.collection.reset();
			this.collection.fetch();
			this.collection.on( 'add error', this.displayUsers, this );

			this.controller.state().on( 'change:search', this.updateContent, this );
			this.controller.state().on( 'change:pagination', this.updateContent, this );

			this.getSelection().on( 'reset', this.clearItems, this );
		},

		updateContent:function( model ) {
			this.removeContent();
			this.collection.reset().fetch();
		},

		removeContent: function() {
	      	_.each( this.users, function( key ) {
	      		key.remove();
		    }, this );

	      	this.users = [];
	    },

		displayUsers:function( model ) {
			var _this = this, 
				selection = this.getSelection();

			if( _.isUndefined( this.users ) )
				this.users = [];

			if( _.isUndefined( this.collection ) || this.collection.length == 0 || ! model ) {
				content = new media.view.WorkMatesUser({
					controller: this.controller,
					model:      new Backbone.Model( {notfound:1} ),
				});

				if( ! this.users[0] ) {
					this.users[0] = content;
					this.views.add( content );
				}

			} else if( model ) {
				content = new media.view.WorkMatesUser({
					controller: this.controller,
					model:      model,
				});
				if( _.isUndefined( this.users[content.model.id] ) ) {
					this.users[content.model.id] = content;
					this.views.add( content );
				}
					
			} else {
				_.each( this.collection.models, function( model ){
					content = new media.view.WorkMatesUser({
						controller: this.controller,
						model:      model,
					});

					_this.users[content.model.id] = content;
					_this.views.add( content );
				} );
			}
			
			selection.each( function( model ) {
				var id = '#user-' + model.get( 'id' );
				this.$el.find( id ).parent( 'li' ).addClass( 'selected details' );
			}, this );

			//search bug
			if( $( '.workmates-users' ).length && $( '.workmates-users' ).length >= 2 )
				$( '.workmates-users' ).find( '#workmates-error' ).parent().remove();
		},

		removeSelectionHandler: function( event ) {

			var target = jQuery( '#' + event.currentTarget.id );
			var id     = target.attr( 'data-id' );

			this.removeFromSelection( target, id );

			event.preventDefault();

		},

		toggleSelectionHandler:function( event ) {
			if ( event.target.href )
				return;

			var target = jQuery( '#' + event.currentTarget.id );
			var id     = target.attr( 'data-id' );

			if ( this.getSelection().get( id ) )
				this.removeFromSelection( target, id );
			else
				this.addToSelection( target, id );
			
		},

		addToSelection: function( target, id ) {

			target.closest( '.workmates-users' ).addClass( 'selected details' );

			this.getSelection().add( this.collection._byId[id] );

			this.controller.state().props.trigger( 'change:selection' );

		},

		removeFromSelection: function( target, id ) {

			target.closest( '.workmates-users' ).removeClass( 'selected details' );

			this.getSelection().remove( this.collection._byId[id] );

			this.controller.state().props.trigger( 'change:selection' );

		},

		getSelection : function() {
			return this.controller.state().props.get( '_all' ).get( 'selection' );
		},

		clearItems: function() {
			this.$el.find( '.workmates-users' ).removeClass( 'selected details' );
		},

	} );

	media.view.WorkMates = wp.media.View.extend( {
		className: 'workmates-frame',
		tagName:'div',
		
		initialize:function() {
			var activeTab = this.options.tab.id,
				activeTpl = this.options.tab.tpl;

			this.createSteps();
			this.createSidebar();
		},

		createSidebar:function() {

			this.sidebar = new media.view.WorkmatesPaginate({
				controller: this.controller
			});

			this.views.add( this.sidebar );

			this.sidebar.set( 'search', new media.view.WorkmatesSearch({
					controller: this.controller,
					collection: this.model.get( 'wmusers' ),
					model:      this.model,
					priority:   80,
			}) );

		},

		createSteps:function() {
			var content;
			
			if( this.options.tab.id == 'wmtab' ) {
				content = this.wmusers = new media.view.WorkMatesUsers({
					controller: this.controller,
					collection: this.model.get( 'wmusers' ),
					model:      this.model,
					tab:        this.options.tab,
				});
			}

			this.views.add( content );
		},

	} );

	media.controller.WorkMates = wp.media.controller.State.extend( {
		defaults: {
			id:       'workmates',
			menu:     'default',
			content:  'wmtab',
			router:   'steps',
			toolbar:  'wm_insert',
			tabs: {
				wmtab: {
					tpl:'workmates',
					text : wml10n.wmTab,
					priority:20,
					id:'wmtab'
				} 
			},
			search: ''
		},

		initialize: function() {
			this.props = new Backbone.Collection();

			this.props.add( new Backbone.Model({
				id        : '_all',
				selection : new Backbone.Collection(),
				mirror    : new Backbone.Collection(),
			}) );

			this.props.on( 'change:selection', this.observeChanges, this );

			if ( ! this.get('wmusers') )
				this.set( 'wmusers', new WorkMatesUsers() );

			this.get( 'wmusers' ).on( 'add', this.fillMirror, this );

		},

		fillMirror:function( model, collection, options ) {
			var mirror = media.frame().state().props.get( '_all' ).get( 'mirror' );
			
			if( !mirror || !mirror.get( model.id ) )
				mirror.add( model );
		},

		activate: function() {
			var wmusers = this.get('wmusers');
			this.frame.on( 'content:render:wmtab', this.manageWhoTab, this );
		},

		observeChanges:function( model ) {
			this.frame.toolbar.get().refresh();
		},

		manageWhoTab:function( wmtab ) {
			this.set( 'content', 'wmtab');
			this.frame.toolbar.get().refresh();
		},

		nextPage:function() {
			this.get('wmusers').options.current_page += 1;
            this.trigger( 'change:pagination' );
		},

		prevPage:function() {
			this.get('wmusers').options.current_page -= 1;
            this.trigger( 'change:pagination' );
		},

		wmInsert:function() {

			var selection = this.props.get( '_all' ).get( 'selection' ),
			workmates_invites = '';

			selection.each( function( model ) {
				workmates_invites += '<li class="workmates-invited" id="uid-' + model.get( 'id' ) + '"> ' + "\n";
				workmates_invites += '<input type="hidden" name="workmates[]" value="' + model.get( 'id' ) + '"/>';
				workmates_invites += '<img src="' + model.get( 'avatar' ) + '" class="avatar" width="50px" heigh="50px">' + "\n";
				workmates_invites += '<h4>' + model.get( 'name' ) + '</h4>'+ "\n";
				workmates_invites += '<span class="activity">&nbsp;</span>' + "\n";
				workmates_invites += '<div class="action">'+ "\n";
				workmates_invites += '<a class="button workmates-remove no-ajax" href="#" data-id="' + model.get( 'id' ) + '"> '+ wml10n.removeInviteBtn +' </a>'+ "\n";
				workmates_invites += '</div>'+ "\n";
				workmates_invites += '</li>'+ "\n";
			}, this );

			wm.media.workmatesShowList();

			$('#workmates-list li').each( function( li ) {
				if( $( this ).find( 'a.workmates-remove' ).hasClass( 'no-ajax') )
					$( this ).remove();
			} );

			$('#workmates-list').append( workmates_invites );

			this.frame.close();
		}

	} );

	media.view.ToolbarWorkMates = wp.media.view.Toolbar.extend({

		initialize: function() {
			_this = this;

			_.defaults( this.options, {
			    event : 'inserter',
			    close : false,
				items : {
				    // See wp.media.view.Button
				    inserter     : {
				        id       : 'wm-button',
				        style    : 'primary',
				        text     : wml10n.wmInsertBtn,
				        priority : 80,
				        click    : function() {
				        	this.controller.state().wmInsert();
						}
				    }
				}
			});

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );


			this.set( 'userSelection', new media.view.WorkmatesUserSelection({
				controller: media.frame(),
				collection: this.controller.state().props.get( '_all' ).get( 'selection' ),
				priority:   -40,
				editable: false
			}) );
		},

		refresh: function() {
			var disabled = true;

			if( this.controller.state().get( 'content') == 'wmtab' ) {
				var selection = this.controller.state().props.get( '_all' ).get( 'selection' );

				disabled = !selection.length;

			}

			this.get( 'inserter' ).model.set( 'disabled', disabled );

		},

	});

	media.view.WorkmatesPaginate = wp.media.view.Toolbar.extend( {
		initialize: function() {
			_this = this;

			_.defaults( this.options, {
			    event : 'pagination',
			    close : false,
				items : {
				    // See wp.media.view.Button
				    next : {
				        id       : 'wm-next',
				        style    : 'secondary',
				        text     : wml10n.wmNextBtn,
				        priority : -60,
				        click    : function() {
				        	this.controller.state().nextPage();
						}
				    },
				    prev : {
				        id       : 'wm-prev',
				        style    : 'secondary',
				        text     : wml10n.wmPrevBtn,
				        priority : -80,
				        click    : function() {
				        	this.controller.state().prevPage();
						}
				    }
				}
			});

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );

			this.controller.state().get( 'wmusers' ).on( 'sync', this.refresh, this );

		},

		refresh: function() {
			var hasmore = hasprev = false,
				total = this.controller.state().get( 'wmusers' ).options.total_page,
				current = this.controller.state().get( 'wmusers' ).options.current_page;

			if( this.controller.state().get( 'content') == 'wmtab' && total > 0 ) {
				hasmore = ( Number( total ) - Number( current ) ) > 0 ? true : false ;
             	hasprev = ( Number( current ) - 1 ) > 0 ? true : false ;
			}

			this.get( 'next' ).model.set( 'disabled', ! hasmore );
			this.get( 'prev' ).model.set( 'disabled', ! hasprev );

		},
	} );

	media.view.WorkmatesSearch = wp.media.view.Search.extend( {

		attributes: {
			type:        'search',
			placeholder: wml10n.wmSrcPlaceHolder
		},

		events: {
			'keyup':  'searchUser',
		},

		initialize: function() {
			wp.media.view.Search.prototype.initialize.apply( this, arguments );
		},

		searchUser: function( event ) {

			if( event.keyCode != 13 )
				return;

			if ( event.target.value )
				this.model.set( 'search', event.target.value );
			else
				this.model.unset('search');

			this.collection.options.current_page = 1;

			this.model.trigger( 'change:search' );
		}
	} );

	/**
	 * wp.media.view.Selection
	 */
	media.view.WorkmatesUserSelection = wp.media.View.extend({
		tagName:   'div',
		className: 'user-selection',
		template:  wp.media.template( 'user-selection' ),

		events: {
			'click .clear-selection': 'clear'
		},

		initialize: function() {
			_.defaults( this.options, {
				editable:  false,
				clearable: true,
			});

			this.collection.on( 'add remove reset', this.refresh, this );
			this.controller.on( 'content:activate', this.refresh, this );
		},

		refresh: function() {

			if ( ! this.$el.children().length )
				return;
			

			var collection = this.collection,
				element = this.$el,
				html = '';

			if( ! collection.length ) {
				element.addClass( 'empty' );
				element.find('.selection-view ul' ).html( '' );
				element.find( '.selection-info .count' ).html( '' );
				return;
			} else {
				element.removeClass( 'empty' );
			}
			
			element.find( '.selection-info .count' ).html( wml10n.invited.replace('%d', collection.length) );

			collection.each( function( model ) {
				var avatar = model.get( 'avatar' );
				var user = model.get( 'id' );
				var name = model.get( 'name' );
				html += '<li class="user-avatar"><img src="' + avatar + '" title="'+name+'"></li>';
				element.find('.selection-view ul' ).html( html );
			}, this );
		},


		clear: function( event ) {
			event.preventDefault();
			this.collection.reset();
			this.controller.state().props.trigger( 'change:selection' );
		}
	});

	media.view.stepsItem = wp.media.view.RouterItem.extend( {

		initialize: function() {
			wp.media.view.RouterItem.prototype.initialize.apply( this, arguments );
		}

	} );

	media.view.stepsRouter = wp.media.view.Router.extend( {
		ItemView:  media.view.stepsItem,

		initialize: function() {
			wp.media.view.Router.prototype.initialize.apply( this, arguments );
		}
	} );

	media.buttonId = '#workmates-select',

	_.extend( media, {
		frame: function() {
			if ( this._frame )
				return this._frame;

			var view,
				_this = this,
				_tabs,
				states = [
					new media.controller.WorkMates( {
						title: wml10n.wmMainTitle,
						id:    'workmates',
					} ),
				];


			this._frame = wp.media( {
				className: 'media-frame',
				states: states,
				state: 'workmates'
			} );

			_.each( states, function( item ){
				if( item.id == 'workmates' ) {
					_tabs = item.attributes.tabs;

					for( tab in item.attributes.tabs ) {
						_this._frame.on( 'content:render:' + tab, _.bind( _this.wmContentRender, this, item.attributes.tabs[tab] ) );
					}
				}
					
			});

			this._frame.on( 'open', this.open );
			
			this._frame.on( 'router:create:steps', this.createRouter, this  );
			this._frame.on( 'router:render:steps', _.bind( this.stepsRouter, this, _tabs ) );
			this._frame.on( 'toolbar:create:wm_insert', _.bind( this.wmToolbarCreate, this, _tabs ) );

			return this._frame;
		},

		createRouter:function( router ) {
			router.view = new media.view.stepsRouter({
				controller: this._frame
			});
		},

		// Routers
		stepsRouter: function( routerItems, view ) {
			var tabs = {};

			for ( var tab in routerItems ) {
			
				tabs[tab] = {
					text : routerItems[tab].text,
					priority : routerItems[tab].priority
				};
			}
			
			view.set( tabs );
		},

		wmContentRender: function( tab ) {

			media.frame().content.set( new wm.media.view.WorkMates( {
				controller : media.frame(),
				model      : media.frame().state(),
				tab        : tab,
			} ) );
			
		},

		wmToolbarCreate:function( tabs, toolbar ) {
			toolbar.view = new wm.media.view.ToolbarWorkMates( {
				controller : media.frame(),
				tab:tabs
			} );

		},

		open: function() {
			$( '.media-modal' ).addClass( 'no-sidebar smaller' );
		},

		workmatesHideList:function() {
			if( $( '#workmates-list li' ).length == 0 ) {
				$( '#workmates-list' ).hide();
				$( '#send-invite-form #submit' ).hide();
			}
		},

		workmatesShowList:function() {
			$( '#workmates-list' ).show();
			$( '#send-invite-form #submit' ).show();
		},

		init: function() {
			media.workmatesHideList();

			$( media.buttonId ).on( 'click', function( e ) {
				e.preventDefault();

				media.frame().open();
			});
		}
	} );

	$( media.init );
	
	$( '#workmates-list').on( 'click', '.workmates-remove', function( e ) {
		e.preventDefault();

		var user_id = $( this ).data( 'id' );

		if( $( this ).hasClass( 'no-ajax' ) ) {

			var all = media.frame().state().props.get( '_all' ).get( 'mirror' );
				selection = media.frame().state().props.get( '_all' ).get( 'selection' );

			if( $( '#user-' + user_id ).length )
				$( '#user-' + user_id ).closest( '.workmates-users' ).removeClass( 'selected details' );
			
			selection.remove( all._byId[user_id] );

			media.frame().state().props.trigger( 'change:selection' );

			$( this ).closest( '.workmates-invited' ).remove();
			wm.media.workmatesHideList();

		} else {
			var _this = $( this );

			wp.ajax.post( 'workmates_uninvite_user', {
                workmate_id: user_id,
                group_id:    wp.media.view.settings.group,
                _wpnonce:    wp.media.view.settings.nonce.workmates,
                
            } ).done( function( success ) {

            	$( '#buddypress div#message' ).slideUp(100).remove();
            	$( '#item-nav' ).prepend( success );
            	$( '#buddypress div#message').hide().fadeIn( 200 );
                _this.closest( '.workmates-invited' ).remove();
                wm.media.workmatesHideList();

            }).fail( function( error ) {

            	$( '#buddypress div#message' ).slideUp(100).remove();
            	$( '#item-nav' ).prepend( error );
            	$( '#buddypress div#message').hide().fadeIn( 200 );

            });
		}
		
	} );

} )( jQuery );
