<div class="wrap">
    <h2><?php _e('Subscribers', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></h2>

    <table cellspacing="0" class="wp-list-table widefat fixed subscribers">
        <thead>
            <tr>
                <th class="manage-column column-id" id="id" scope="col">
                    <span><?php _e('Id', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></span>
                </th>
                <th class="manage-column column-email" id="email" scope="col">
                    <span><?php _e('Email', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></span>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th class="manage-column column-id" id="id" scope="col">
                    <span><?php _e('Id', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></span>
                </th>
                <th class="manage-column column-email" id="email" scope="col">
                    <span><?php _e('Email', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></span>
                </th>
            </tr>
        </tfoot>
        <tbody id="the-list">
            <?php
            global $hibridoSubscribers;
            $rows = $hibridoSubscribers->getAll();
            ?>
            <?php if ( ! $rows) : ?>
                <tr class="no-items">
                    <td colspan="3" class="colspanchange"><?php _e('No subscribers have been added to the list', HIBRIDO_SUBSCRIBERS_TEXT_DOMAIN);?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($rows as $k => $row) : ?>
                    <tr<?php echo (($k % 2) == 0) ? ' class="alternate"' : '';?>>
                        <td>
                            <?php echo esc_js(esc_html($row->id));?>
                        </td>
                        <td>
                            <?php echo esc_js(esc_html($row->email));?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>