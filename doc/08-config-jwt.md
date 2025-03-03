# JWT Configuration

JWT is used to send a signed token to Grafana, so the graphs only load if the JWT token is validated by Grafana. If the token is expired or not valid, Grafana will redirect the iframe to the login page.

### Icinga configuration
In the Icinga Web configuration:

1. Change "Grafana access" to "iFrame" and "Enable JWT"

2. Choose an expiration, issuer and user.
    - A short expiration is recommended, because the token is being sent in the URL.
    - Set an issuer, for a better validation. Must be set the same on both sides. The default is empty, no issuer.
    - Use an existing Grafana username so the graphs are accessed using that user.

3. When you save the configuration, the RSA keys will be created at /etc/icingaweb2/modules/grafana/ (jwt.key.priv and jwt.key.pub).
    - For now, other directories are not supported, the filenames are hard coded in the file library/Grafana/Helpers/JwtToken.php.
    - If any kind of errors happens while creating the keys (e.g. permission denied), you will have to create the keys and copy them to the directory /etc/icingaweb2/modules/grafana/, use the commands below.

4. The private key (jwt.key.priv), should kept safe, Grafana server only needs the public key. If you have multiple Icinga Web servers, copy the keys to the other servers.

```
openssl genrsa -out /etc/icingaweb2/modules/grafana/jwt.key.priv 2048

openssl rsa -in /etc/icingaweb2/modules/grafana/jwt.key.priv -pubout -outform PEM -out /etc/icingaweb2/modules/grafana/jwt.key.pub
```

### Grafana

The configuration options for Grafana JWT Auth can be found here: [https://grafana.com/docs/grafana/latest/setup-grafana/configure-security/configure-authentication/jwt/](https://grafana.com/docs/grafana/latest/setup-grafana/configure-security/configure-authentication/jwt/).

Example `grafana.ini`:

```
[auth.jwt]
# By default, auth.jwt is disabled.
enabled = true

# HTTP header to look into to get a JWT token.
header_name = X-JWT-Assertion

# Specify a claim to use as a username to sign in.
username_claim = sub

# Specify a claim to use as an email to sign in.
email_claim = sub

# enable JWT authentication in the URL
url_login = true

# PEM-encoded key file in PKIX, PKCS #1, PKCS #8 or SEC 1 format.
key_file = /etc/grafana/icinga.pem

# This can be seen as a required "subset" of a JWT Claims Set.
# expect_claims = {"iss": "https://icinga.yourdomain"}

# role_attribute_path = contains(roles[*], 'admin') && 'Admin' || contains(roles[*], 'editor') && 'Editor' || 'Viewer'

# To skip the assignment of roles and permissions upon login via JWT and handle them via other mechanisms like the user interface, we can skip the organization role synchronization with the following configuration.
skip_org_role_sync = true
```

1. Read the docs, and configure your grafana.ini

2. Copy the **public key** from Icinga (/etc/icingaweb2/modules/grafana/jwt.key.pub) to the path configured in "key_file".

3. Enable url_login, header_name and username_claim/email_claim these options are required.

4. Enable allow_embedding in the security section.

5. Restart Grafana
