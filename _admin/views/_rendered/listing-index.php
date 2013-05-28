    <section class="main__content">
        <div class="container">
            <div class="page_title">

                <input type="hidden" class="js-title-raw" value="<?php echo $table; ?>" />
                <p class="page_name"><span class="js-title"><?php echo ucfirst(str_replace( "_", " ", $table)); ?></span> listing</p>
                <a href="#" class="js-reset">Reset</a>


                <a href="#" class="js-filter">Filter</a>
            </div>

            <div class="filter js-filter-container">
                <dl class="filter_options">
                    <?php foreach ( $columns as $column ): ?>
                        <dt><?php echo ucfirst(str_replace("_", " ", $column)); ?></dt>
                        <dd><input type="text" data-type="<?php echo $column; ?>" class="js-search-boxes <?php echo ( $column == 'create_date' ? 'js-date' : '' ); ?>" /></dd>
                    <?php endforeach; ?> 
                </dl>
                <input type="submit" class="search-button js-search" value="search">
            </div>
            <div class="holder">
                <div class="js-error"></div>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_listing js-table">
                  <thead>
                    <tr>
                      <th></th>
                      <?php foreach ( $columns as $column ): ?>
                        <th><?php echo ucfirst(str_replace("_", " ", $column)); ?></th>
                      <?php endforeach; ?> 
                      <th><a href="<?php echo DIRECTORY; ?>admin/<?php echo $table; ?>/edit" class="add-button"><i class="icon-plus-sign"></i> Add</a></th>
                    </tr>
                  </thead>
                  <tbody class="js-sortable js-body">
                    <?php foreach( $data as $key => $value ): ?>
                        <tr class="js-row-<?php echo $data[$key]['id']; ?>" id="<?php echo $data[$key]['id']; ?>">
                            <td><input type="checkbox" name="selected" value="yes" class="checkbox js-delete-checkbox"></td>
                            <!-- For loop through the columns and hitting the right data column from the columns array-->
                            <?php for( $i=0; $i<count($columns)-1; $i++ ): ?>

                                <td><?php echo $data[$key][$columns[$i]]; ?></td>
                                
                                
                            <?php endfor; ?> 
                            <td><?php echo date( "d/m/y H:i", strtotime ( $data[$key]['create_date'] ) ); ?></td>    
                            <td>
                                <a href="<?php echo DIRECTORY; ?>admin/<?php echo $table; ?>/edit/<?php echo $data[$key]['id']; ?>" class="edit_icon icon-pencil"></a>
                                
                                <a href="#" class="remove_icon icon-remove-sign js-delete-popup" data-id="<?php echo $data[$key]['id']; ?>"></a>
                            </td>
                        </tr>
                    <?php endforeach; ?> 
                  </tbody>
                </table>
                <input type="button" class="delete-button js-delete-popup" value="Delete">
            </div>
        </div>
    </section>