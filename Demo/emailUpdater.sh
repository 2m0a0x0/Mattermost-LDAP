#!/bin/bash

# this script should be called after we are authorized with gitlab when we render the page authorize
# may be a revert will be nice if smt happens

update_email() {
    psql -d mattermost_test -U mmuser -c "
        -- Update the existing email address
        UPDATE users SET email='$1@h-ka.de1' WHERE email='$1@h-ka.de';
    "
    echo "Database updated successfully!"
}

update_email "$1"
