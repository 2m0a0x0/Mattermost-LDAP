#!/bin/bash

# Function to update the database
update_database() {
    # Connect to your PostgreSQL database
    psql -d mattermost_test -U mmuser -c "
        -- Update the existing email address
        UPDATE users SET email='$1@h-ka.de' WHERE email='$1@h-ka.de';

        -- Select the id of the entry with email 'username'
        SELECT id INTO TEMP TABLE temp_id FROM users WHERE email = '$1@h-ka.de1';
        
        -- Delete the entry with the email 'username'
        DELETE FROM users WHERE email = '$1@h-ka.de1';
        
        -- Update the id and username of the entry with email 'username'
        UPDATE users SET id = (SELECT id FROM temp_id), username = '$1' WHERE email = '$1@h-ka.de';
        
        -- Drop temporary table
        DROP TABLE temp_id;
    "
    echo "Database updated successfully!"
}

# Call the function with the first argument passed to the script
update_database "$1"
