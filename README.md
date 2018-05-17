Here is a set of PHP classes to handle Basic/Digest HTTP Authentication. Each class depends each others incrementally : <code>DigestSess extends DigestQOP extends Digest extends Basic</code> (the most left is the strongest but the slowest, Basic mean no security).

Tests and examples
======

Look at the <code>tests/</code> directory to get an example of working and commented scripts using <code>$_SESSION</code> of each class.

Use the following command to copy tests files to your web server with accompagned <code>.htaccess</code> file to test mutiple uris like <code>://localhost:80/path_to/www_*[0-9]+\.php</code> (you must have <code>bash</code>).

	./copy_tests.sh path_to_webserver_directory

Quick Description
======

A property <code>$realm</code> can be overridden, it should describe a group/type of authorization, in <code>Basic</code> it have no impact unless you decide it, but for <code>Digest</code> it will have one.

Use <code>$data</code> property to add extra data when authorization is asked.

Other properties are overridable but you must know what you do. Actually it's possible to use other hash algorithm than MD5 and MD5-sess but browsers doesn't support it yet if i'm not wrong.

Use <code>->ask()</code> method to ask authorization using <code>header()</code> function (output of the script should be empty).

Each class require an <code>->isLogged()</code> method which determine if the client were already logged or not. It will also bypass the call to <code>->isAuthorized()</code> and for Digest <code>->getSecret()</code> too because it will be called from the internally declared <code>->isAuthorized()</code> method.

The Basic class require an <code>->isAuthorized()</code> method wich must use of PHP global variables (<code>$_SERVER['PHP_AUTH_USER']</code> and <code>$_SERVER['PHP_AUTH_PW']</code>) to authenticate the client.

The Digest classes require a <code>->getSecret($digest)</code> method wich will receive the digest parameters as array and will retreive from any source (ex: MySQL database) a secret token to be used for authentication.

The Digest classes have an <code>->createSecret($username, $pass)</code> which generate the secret token to be stored to use with the current <code>$realm</code> of the instance, it's th same for all variants so you can for example offer to you users the choose of one of the 3 way to authenticate with no need to store differents secret tokens.

Also note that the <code>Digest</code> classes are using an <code>http_parse_params()</code> function to parse Digest params which can be overriden by the <code>pecl_http</code> extension (not tested !). 

Usage
======

Include one of the class you want to use :

	require_once "src/Basic.php"

Define a Class wich extend one of the base class (Basic/Digest/DigestQOP/DigestSess).

For <code>Basic</code> write a class like this : 

	class HttpAuth extends KevinMuret\HttpAuth\Basic {
	  public function isLogged()// Return a boolean (Check if it has already been logged).
	  public function isAuthorized()// Return a boolean (Check if user exists and the password is valid).
	}

For any one of the <code>Digest</code> familly write a class like this : 

	class HttpAuth extends KevinMuret\HttpAuth\Digest {
	  public function isLogged()// Return a boolean (Check if it has already been logged)
	  public function getSecret($digest)// Return a string (Retreive the secret token for the specified user reading value of 'username' key from $digest array)
	}

In your scripts instance it this way for <code>Basic</code> (with an optional <code>$realm</code> value).

	$auth = new HttpAuth()

For <code>Digest</code> it's a little bit more sofisticated because there is at least two more parameters that have to be given when authorization has been asked before.

	$auth = new HttpAuth($realm, $nonce, $secret)

And for <code>DigestQOP</code> and <code>DigestSess</code> there is one more which is the request counter (it's recommand to increment it just before).

	$auth = new HttpAuth($realm, $nonce, ++$nc, $secret)

For <code>Digest</code> classes you will have next to store somewhere the <code>$nonce</code> using <code>->nonce()</code> method to retreive it, and for QOP and Sess variants you hve to initialize a counter which will be incremented and used for comparaison with the <code>$nc</code> given by the client (this will generate different header on each request and increase security).

For next you should look at the <code>$status</code> property which can be one of these 4 constants :

- <code>$auth::NOTLOGGED</code> Login should be asked here.
- <code>$auth::FAILED</code> Authentication has failed !
- <code>$auth::JUSTLOGGED</code> Login should be started here. (For <code>Digest</code> you have to store the <code>$secret</code> using <code>->secret()</code> method to retreive it.
- <code>$auth::LOGGED</code> Login were successfull !
