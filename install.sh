#!/usr/bin/env bash
set -euo pipefail

if ( ! command -v htmlq > /dev/null )
then
    echo 'The htmlq command is required for this script.'
    exit 1
fi

# Get the login page HTML, and the login URL from the form.
# This is necessary to get a URL with a valid login_token query parameter.
# We also need a cookie jar file to store the session cookie.
loginHtml="$(curl -s 'http://localhost/administration/index.php?route=common/login' --cookie-jar 'cookies.txt')"
loginUrl="$(echo "$loginHtml" | htmlq -a 'action' '#form-login')"

# Make the login call, this returns a JSON object: `{"redirect":"http://..."}
# This URL contains a user_token which we need to store.
loginRedirect="$(curl -sX POST "$loginUrl" -d 'username=admin' -d 'password=parcelpro1' --cookie 'cookies.txt')"
userToken="$(echo "$loginRedirect" | sed -nE 's/.*user_token=([^"]*).*/\1/p')"

function ocApi() {
    curl -s "http://localhost/administration/index.php?route=$1&user_token=$userToken" --cookie 'cookies.txt'
    echo
}

function findExtensionId() {
    # List the installed extensions.
    # Find all rows from the table, making sure each row is a single line.
    local listRows
    listRows="$(ocApi 'marketplace/installer.list' | tr '\n' ' ' | htmlq 'tbody tr')"

    # Find the row containing the Parcel Pro extension.
    while read -r row
    do
        # Get the extension name, trim it.
        local name
        name="$(echo "$row" | htmlq -t 'a' | xargs)"

        # Check if we found the Parcel Pro extension.
        if [ "$name" = 'Parcel Pro' ]
        then
            # Get the extension id from a URL query parameter.
            echo "$row" | sed -nE 's/.*extension_install_id=([^"]*).*/\1/p'
        fi
    done < <(echo "$listRows")
}

# Get the current extension id, check if it exists.
extensionId="$(findExtensionId)"
if [ -n "${extensionId+x}" ]
then
    echo "Removing existing extension (id $extensionId)"

    # Uninstall the module and shipping extensions.
    ocApi 'extension/module.uninstall&extension=parcelpro&code=parcelpro'
    ocApi 'extension/shipping.uninstall&extension=parcelpro&code=parcel_pro'

    # Uninstall and delete the extension.
    ocApi "marketplace/installer.uninstall&extension_install_id=$extensionId" > /dev/null
    ocApi "marketplace/installer.delete&extension_install_id=$extensionId"
fi

echo 'Building extension'
./build.sh > /dev/null

# Upload the extension zip and install it.
echo 'Uploading and installing extension'
curl -s -X POST "http://localhost/administration/index.php?route=marketplace/installer.upload&user_token=$userToken" --cookie 'cookies.txt' \
    -F 'file=@parcelpro.ocmod.zip' > /dev/null

# Get the new extension id.
extensionId="$(findExtensionId)"
echo "Installing new extension (id $extensionId)"

# Install the extension.
ocApi "marketplace/installer.install&extension_install_id=$extensionId" > /dev/null

# Install the module and shipping extensions.
ocApi 'extension/module.install&extension=parcelpro&code=parcelpro'
ocApi 'extension/shipping.install&extension=parcelpro&code=parcel_pro'

# Remove the cookie jar.
rm 'cookies.txt'

echo 'Done!'
