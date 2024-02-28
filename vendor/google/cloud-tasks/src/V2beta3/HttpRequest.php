<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/tasks/v2beta3/target.proto

namespace Google\Cloud\Tasks\V2beta3;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * HTTP request.
 * The task will be pushed to the worker as an HTTP request. If the worker
 * or the redirected worker acknowledges the task by returning a successful HTTP
 * response code ([`200` - `299`]), the task will be removed from the queue. If
 * any other HTTP response code is returned or no response is received, the
 * task will be retried according to the following:
 * * User-specified throttling: [retry
 * configuration][google.cloud.tasks.v2beta3.Queue.retry_config],
 *   [rate limits][google.cloud.tasks.v2beta3.Queue.rate_limits], and the
 *   [queue's state][google.cloud.tasks.v2beta3.Queue.state].
 * * System throttling: To prevent the worker from overloading, Cloud Tasks may
 *   temporarily reduce the queue's effective rate. User-specified settings
 *   will not be changed.
 *  System throttling happens because:
 *   * Cloud Tasks backs off on all errors. Normally the backoff specified in
 *     [rate limits][google.cloud.tasks.v2beta3.Queue.rate_limits] will be used.
 *     But if the worker returns `429` (Too Many Requests), `503` (Service
 *     Unavailable), or the rate of errors is high, Cloud Tasks will use a
 *     higher backoff rate. The retry specified in the `Retry-After` HTTP
 *     response header is considered.
 *   * To prevent traffic spikes and to smooth sudden increases in traffic,
 *     dispatches ramp up slowly when the queue is newly created or idle and
 *     if large numbers of tasks suddenly become available to dispatch (due to
 *     spikes in create task rates, the queue being unpaused, or many tasks
 *     that are scheduled at the same time).
 *
 * Generated from protobuf message <code>google.cloud.tasks.v2beta3.HttpRequest</code>
 */
class HttpRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The full url path that the request will be sent to.
     * This string must begin with either "http://" or "https://". Some examples
     * are: `http://acme.com` and `https://acme.com/sales:8080`. Cloud Tasks will
     * encode some characters for safety and compatibility. The maximum allowed
     * URL length is 2083 characters after encoding.
     * The `Location` header response from a redirect response [`300` - `399`]
     * may be followed. The redirect is not counted as a separate attempt.
     *
     * Generated from protobuf field <code>string url = 1;</code>
     */
    private $url = '';
    /**
     * The HTTP method to use for the request. The default is POST.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.HttpMethod http_method = 2;</code>
     */
    private $http_method = 0;
    /**
     * HTTP request headers.
     * This map contains the header field names and values.
     * Headers can be set when the
     * [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     * These headers represent a subset of the headers that will accompany the
     * task's HTTP request. Some HTTP request headers will be ignored or replaced.
     * A partial list of headers that will be ignored or replaced is:
     * * Any header that is prefixed with "X-CloudTasks-" will be treated
     * as service header. Service headers define properties of the task and are
     * predefined in CloudTask.
     * * Host: This will be computed by Cloud Tasks and derived from
     *   [HttpRequest.url][google.cloud.tasks.v2beta3.HttpRequest.url].
     * * Content-Length: This will be computed by Cloud Tasks.
     * * User-Agent: This will be set to `"Google-Cloud-Tasks"`.
     * * `X-Google-*`: Google use only.
     * * `X-AppEngine-*`: Google use only.
     * `Content-Type` won't be set by Cloud Tasks. You can explicitly set
     * `Content-Type` to a media type when the
     *  [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     *  For example, `Content-Type` can be set to `"application/octet-stream"` or
     *  `"application/json"`.
     * Headers which can have multiple values (according to RFC2616) can be
     * specified using comma-separated values.
     * The size of the headers must be less than 80KB.
     *
     * Generated from protobuf field <code>map<string, string> headers = 3;</code>
     */
    private $headers;
    /**
     * HTTP request body.
     * A request body is allowed only if the
     * [HTTP method][google.cloud.tasks.v2beta3.HttpRequest.http_method] is POST,
     * PUT, or PATCH. It is an error to set body on a task with an incompatible
     * [HttpMethod][google.cloud.tasks.v2beta3.HttpMethod].
     *
     * Generated from protobuf field <code>bytes body = 4;</code>
     */
    private $body = '';
    protected $authorization_header;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $url
     *           Required. The full url path that the request will be sent to.
     *           This string must begin with either "http://" or "https://". Some examples
     *           are: `http://acme.com` and `https://acme.com/sales:8080`. Cloud Tasks will
     *           encode some characters for safety and compatibility. The maximum allowed
     *           URL length is 2083 characters after encoding.
     *           The `Location` header response from a redirect response [`300` - `399`]
     *           may be followed. The redirect is not counted as a separate attempt.
     *     @type int $http_method
     *           The HTTP method to use for the request. The default is POST.
     *     @type array|\Google\Protobuf\Internal\MapField $headers
     *           HTTP request headers.
     *           This map contains the header field names and values.
     *           Headers can be set when the
     *           [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     *           These headers represent a subset of the headers that will accompany the
     *           task's HTTP request. Some HTTP request headers will be ignored or replaced.
     *           A partial list of headers that will be ignored or replaced is:
     *           * Any header that is prefixed with "X-CloudTasks-" will be treated
     *           as service header. Service headers define properties of the task and are
     *           predefined in CloudTask.
     *           * Host: This will be computed by Cloud Tasks and derived from
     *             [HttpRequest.url][google.cloud.tasks.v2beta3.HttpRequest.url].
     *           * Content-Length: This will be computed by Cloud Tasks.
     *           * User-Agent: This will be set to `"Google-Cloud-Tasks"`.
     *           * `X-Google-*`: Google use only.
     *           * `X-AppEngine-*`: Google use only.
     *           `Content-Type` won't be set by Cloud Tasks. You can explicitly set
     *           `Content-Type` to a media type when the
     *            [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     *            For example, `Content-Type` can be set to `"application/octet-stream"` or
     *            `"application/json"`.
     *           Headers which can have multiple values (according to RFC2616) can be
     *           specified using comma-separated values.
     *           The size of the headers must be less than 80KB.
     *     @type string $body
     *           HTTP request body.
     *           A request body is allowed only if the
     *           [HTTP method][google.cloud.tasks.v2beta3.HttpRequest.http_method] is POST,
     *           PUT, or PATCH. It is an error to set body on a task with an incompatible
     *           [HttpMethod][google.cloud.tasks.v2beta3.HttpMethod].
     *     @type \Google\Cloud\Tasks\V2beta3\OAuthToken $oauth_token
     *           If specified, an
     *           [OAuth token](https://developers.google.com/identity/protocols/OAuth2)
     *           will be generated and attached as an `Authorization` header in the HTTP
     *           request.
     *           This type of authorization should generally only be used when calling
     *           Google APIs hosted on *.googleapis.com.
     *     @type \Google\Cloud\Tasks\V2beta3\OidcToken $oidc_token
     *           If specified, an
     *           [OIDC](https://developers.google.com/identity/protocols/OpenIDConnect)
     *           token will be generated and attached as an `Authorization` header in the
     *           HTTP request.
     *           This type of authorization can be used for many scenarios, including
     *           calling Cloud Run, or endpoints where you intend to validate the token
     *           yourself.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Tasks\V2Beta3\Target::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The full url path that the request will be sent to.
     * This string must begin with either "http://" or "https://". Some examples
     * are: `http://acme.com` and `https://acme.com/sales:8080`. Cloud Tasks will
     * encode some characters for safety and compatibility. The maximum allowed
     * URL length is 2083 characters after encoding.
     * The `Location` header response from a redirect response [`300` - `399`]
     * may be followed. The redirect is not counted as a separate attempt.
     *
     * Generated from protobuf field <code>string url = 1;</code>
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Required. The full url path that the request will be sent to.
     * This string must begin with either "http://" or "https://". Some examples
     * are: `http://acme.com` and `https://acme.com/sales:8080`. Cloud Tasks will
     * encode some characters for safety and compatibility. The maximum allowed
     * URL length is 2083 characters after encoding.
     * The `Location` header response from a redirect response [`300` - `399`]
     * may be followed. The redirect is not counted as a separate attempt.
     *
     * Generated from protobuf field <code>string url = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setUrl($var)
    {
        GPBUtil::checkString($var, True);
        $this->url = $var;

        return $this;
    }

    /**
     * The HTTP method to use for the request. The default is POST.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.HttpMethod http_method = 2;</code>
     * @return int
     */
    public function getHttpMethod()
    {
        return $this->http_method;
    }

    /**
     * The HTTP method to use for the request. The default is POST.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.HttpMethod http_method = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setHttpMethod($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Tasks\V2beta3\HttpMethod::class);
        $this->http_method = $var;

        return $this;
    }

    /**
     * HTTP request headers.
     * This map contains the header field names and values.
     * Headers can be set when the
     * [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     * These headers represent a subset of the headers that will accompany the
     * task's HTTP request. Some HTTP request headers will be ignored or replaced.
     * A partial list of headers that will be ignored or replaced is:
     * * Any header that is prefixed with "X-CloudTasks-" will be treated
     * as service header. Service headers define properties of the task and are
     * predefined in CloudTask.
     * * Host: This will be computed by Cloud Tasks and derived from
     *   [HttpRequest.url][google.cloud.tasks.v2beta3.HttpRequest.url].
     * * Content-Length: This will be computed by Cloud Tasks.
     * * User-Agent: This will be set to `"Google-Cloud-Tasks"`.
     * * `X-Google-*`: Google use only.
     * * `X-AppEngine-*`: Google use only.
     * `Content-Type` won't be set by Cloud Tasks. You can explicitly set
     * `Content-Type` to a media type when the
     *  [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     *  For example, `Content-Type` can be set to `"application/octet-stream"` or
     *  `"application/json"`.
     * Headers which can have multiple values (according to RFC2616) can be
     * specified using comma-separated values.
     * The size of the headers must be less than 80KB.
     *
     * Generated from protobuf field <code>map<string, string> headers = 3;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * HTTP request headers.
     * This map contains the header field names and values.
     * Headers can be set when the
     * [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     * These headers represent a subset of the headers that will accompany the
     * task's HTTP request. Some HTTP request headers will be ignored or replaced.
     * A partial list of headers that will be ignored or replaced is:
     * * Any header that is prefixed with "X-CloudTasks-" will be treated
     * as service header. Service headers define properties of the task and are
     * predefined in CloudTask.
     * * Host: This will be computed by Cloud Tasks and derived from
     *   [HttpRequest.url][google.cloud.tasks.v2beta3.HttpRequest.url].
     * * Content-Length: This will be computed by Cloud Tasks.
     * * User-Agent: This will be set to `"Google-Cloud-Tasks"`.
     * * `X-Google-*`: Google use only.
     * * `X-AppEngine-*`: Google use only.
     * `Content-Type` won't be set by Cloud Tasks. You can explicitly set
     * `Content-Type` to a media type when the
     *  [task is created][google.cloud.tasks.v2beta3.CloudTasks.CreateTask].
     *  For example, `Content-Type` can be set to `"application/octet-stream"` or
     *  `"application/json"`.
     * Headers which can have multiple values (according to RFC2616) can be
     * specified using comma-separated values.
     * The size of the headers must be less than 80KB.
     *
     * Generated from protobuf field <code>map<string, string> headers = 3;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setHeaders($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->headers = $arr;

        return $this;
    }

    /**
     * HTTP request body.
     * A request body is allowed only if the
     * [HTTP method][google.cloud.tasks.v2beta3.HttpRequest.http_method] is POST,
     * PUT, or PATCH. It is an error to set body on a task with an incompatible
     * [HttpMethod][google.cloud.tasks.v2beta3.HttpMethod].
     *
     * Generated from protobuf field <code>bytes body = 4;</code>
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * HTTP request body.
     * A request body is allowed only if the
     * [HTTP method][google.cloud.tasks.v2beta3.HttpRequest.http_method] is POST,
     * PUT, or PATCH. It is an error to set body on a task with an incompatible
     * [HttpMethod][google.cloud.tasks.v2beta3.HttpMethod].
     *
     * Generated from protobuf field <code>bytes body = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setBody($var)
    {
        GPBUtil::checkString($var, False);
        $this->body = $var;

        return $this;
    }

    /**
     * If specified, an
     * [OAuth token](https://developers.google.com/identity/protocols/OAuth2)
     * will be generated and attached as an `Authorization` header in the HTTP
     * request.
     * This type of authorization should generally only be used when calling
     * Google APIs hosted on *.googleapis.com.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.OAuthToken oauth_token = 5;</code>
     * @return \Google\Cloud\Tasks\V2beta3\OAuthToken|null
     */
    public function getOauthToken()
    {
        return $this->readOneof(5);
    }

    public function hasOauthToken()
    {
        return $this->hasOneof(5);
    }

    /**
     * If specified, an
     * [OAuth token](https://developers.google.com/identity/protocols/OAuth2)
     * will be generated and attached as an `Authorization` header in the HTTP
     * request.
     * This type of authorization should generally only be used when calling
     * Google APIs hosted on *.googleapis.com.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.OAuthToken oauth_token = 5;</code>
     * @param \Google\Cloud\Tasks\V2beta3\OAuthToken $var
     * @return $this
     */
    public function setOauthToken($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Tasks\V2beta3\OAuthToken::class);
        $this->writeOneof(5, $var);

        return $this;
    }

    /**
     * If specified, an
     * [OIDC](https://developers.google.com/identity/protocols/OpenIDConnect)
     * token will be generated and attached as an `Authorization` header in the
     * HTTP request.
     * This type of authorization can be used for many scenarios, including
     * calling Cloud Run, or endpoints where you intend to validate the token
     * yourself.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.OidcToken oidc_token = 6;</code>
     * @return \Google\Cloud\Tasks\V2beta3\OidcToken|null
     */
    public function getOidcToken()
    {
        return $this->readOneof(6);
    }

    public function hasOidcToken()
    {
        return $this->hasOneof(6);
    }

    /**
     * If specified, an
     * [OIDC](https://developers.google.com/identity/protocols/OpenIDConnect)
     * token will be generated and attached as an `Authorization` header in the
     * HTTP request.
     * This type of authorization can be used for many scenarios, including
     * calling Cloud Run, or endpoints where you intend to validate the token
     * yourself.
     *
     * Generated from protobuf field <code>.google.cloud.tasks.v2beta3.OidcToken oidc_token = 6;</code>
     * @param \Google\Cloud\Tasks\V2beta3\OidcToken $var
     * @return $this
     */
    public function setOidcToken($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Tasks\V2beta3\OidcToken::class);
        $this->writeOneof(6, $var);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizationHeader()
    {
        return $this->whichOneof("authorization_header");
    }

}

