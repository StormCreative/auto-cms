define(['../utils/hogan', 'Backbone'], function(hogan){
    
    return Backbone.View.extend({

        initialize: function () {

            //Apply the juery ui datepicker to any input with the class of js-date
            $( '.js-date' ).datepicker({ dateFormat: 'dd-mm-yy' });

            //Some properties
            var table = $( '.js-title-raw' ).val();
            this.table = table.toLowerCase();
            this.error = $( '.js-error' );

            this.delete_id;

            this.filter_results;

            this.site_path = window.site_path.replace( "_admin", "admin" );
        },

        el: $( 'body' ),

        events: {
            //Event type .element-name : function-name
            'click .js-filter'         : 'toggle_filter',
            'click .js-approve'        : 'approve_row',
            'click .js-delete-popup'   : 'show_popup',
            'click .js-close'          : 'hide_popup',
            'click .js-confirm-delete' : 'action_delete',
            'click .js-search'         : 'action_filter',
            'click .js-reset'          : 'reset'
        },

        toggle_filter: function ( e ) {

            var filter_container = $( '.js-filter-container' );
            filter_container.slideToggle( 500 );
        },

        action_filter: function ( e ) {

            //Reset the results before we filter so nothing messes up
            this.reset();

            var search_boxes = $( '.js-search-boxes' ),
                count = search_boxes.length,
                params = [];

            for ( i = 0; i < count; i++ ) {

                var type = $(search_boxes[i]).attr( 'data-type' );
                params.push({ type: type.trim(), value: $(search_boxes[i]).val() });
            }

            $.ajax({ url: this.site_path + 'AJAX_listing/filter',
                     data: { table: this.table, params: params },
                     type: 'POST',
                     dataType: 'JSON',
                     async: false,
                     success: _.bind(function ( data ) {
                        this.set_filter_results ( data );
                     }, this )
            });

            this.organise_filter_results();
        },

        set_filter_results: function ( data ) {
            this.filter_results = data;
        },

        organise_filter_results: function () {
            var results_container = $( '.js-body' );

            if ( this.filter_results.status == '200' )
            {
                var ids = [],
                results_count = this.filter_results.data.length,
                result_ids = [];

                /**
                 * Im going to try and loop through each row and if the ID is not found in the results then I am going to remove that row
                 */
                $.each( $(results_container).children(), function ( key, item ) {
                    ids.push( $( item ).attr( 'id' ) );
                });

                for ( i = 0; i < results_count; i++ ) {
                    result_ids.push( this.filter_results.data[ i ].id );
                }

                for ( i in ids ) {
                    if ( $.inArray( ids[ i ], result_ids ) == -1 ) {
                        $( '.js-row-' + ids[ i ] ).hide();
                    }
                }
            }
            else 
            {
                this.error.html( '<p class="deleted_message">' + this.filter_results.msg + '</p>' );
                $( '.js-table' ).hide();
            }
        },

        approve_row: function ( e ) {
            var target = e.target,
                row_id,
                approved;

            row_id = $( target ).parent().parent().attr( 'id' );

            //Check if the row has already been approved so we know what to sent to the database
            approved = $( target ).attr( 'data-approved' );

            if ( approved == '1' )
                approve = '0';
            else
                approve = '1';

            $.ajax({ url: this.site_path + 'AJAX_listing/approve',
                     data: { id: row_id, table: this.table, approve: approve },
                     type: 'POST',
                     dataType: 'JSON',
                     success: function ( data ) {

                        if ( data[ 0 ] == 'done' ) {
                            if ( data[ 'approved' ] == '1' )
                            {
                                $(target).css( 'background-color', 'green' );
                                $(target).attr( 'data-approved', '1' );
                            }
                            else
                            {
                                $(target).css( 'background-color', '' );
                                $(target).attr( 'data-approved', '0' );
                            }
                        }
                        else if ( data[ 0 ] == 'not done' )
                            this.error.html( '<p class="deleted_message">This item could not be approved. Refresh the page and try again.</p>' );
                     }
            });

            e.preventDefault();
        },

        show_popup: function ( e ) {
            $('.overlay').fadeIn('normal');

            //We need to get what is being deleted
            //If it is a single entry this will be a ID
            //If it is the multiple delete button then we need to take note of this for later
            var target = e.target,
                id = $(target).attr( 'data-id' );

            //If the id isn't undefined use it otherwise set a identifier that more processing is needed at a later point
            if ( typeof ( id ) != 'undefined' )
                this.delete_id = id;
            else
                this.delete_id = 'multi';

            e.preventDefault();
        },

        hide_popup: function ( e ) {
            $('.overlay').fadeOut('normal');
        },

        action_delete: function ( e ) {

            var ids = [];

            if ( this.delete_id == 'multi' ) {
                //At this point we need to grab the id from every checkbox that is checked
                var checkboxes = $( '.js-delete-checkbox:checked' ),
                    count = checkboxes.length;

                for ( i = 0; i < count; i++ ) {
                    ids.push( $( checkboxes[i] ).parent().parent().attr( 'id' ) );
                }
            }
            else 
                ids.push( this.delete_id );

            console.log ( this.table );

            $.ajax({ url: this.site_path + 'AJAX_listing/delete',
                     data: { table: this.table, ids: ids },
                     type: 'POST',
                     dataType: 'JSON',
                     success: _.bind(function ( data ) {

                        if ( data[ 0 ] == 'deleted' ) {
                            var id_count = ids.length;

                            for ( x = 0; x < id_count; x++ ) {
                                $( '.js-row-' + ids[ x ] ).remove();
                            }

                            this.hide_popup();

                            this.error.html( '<p class="success_message">' + id_count + ' items have been deleted successfully</p>' );
                        }
                        else 
                            this.error.html( '<p class="deleted_message">The item(s) could not be deleted at this time</p>' );

                     }, this )
             });

            e.preventDefault();
        },

        reset: function ( e ) {

            //If the search returned no results the table will be hidden so we need to show it
            $( '.js-table' ).show();
            //Clear the error message just in case it has been set
            this.error.html( '' );

            $.each( $( '.js-body' ).children(), function ( key, item ) {
                $( item ).show();
            });

            if ( !!e )
                e.preventDefault();
        }
    });
});