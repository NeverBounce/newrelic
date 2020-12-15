<?php declare(strict_types=1);
/**
 * Copyright 2013 In-Touch Insight Systems
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Intouch\Newrelic;

use Intouch\Newrelic\Handler\DefaultHandler;
use Intouch\Newrelic\Handler\Handler;
use Intouch\Newrelic\Handler\NullHandler;

/**
 * Wrapper class for the NewRelic PHP Agent API methods.
 *
 * This class is designed to work with PHP Agent version 3.1+ and requires PHP 5.3+
 *
 * @package Intouch\Newrelic
 */
class Newrelic
{
    /**
     * @var bool
     */
    protected $installed;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * Allows pass-through if NewRelic is not installed (default) or optionally throws a runtime exception is the
     * NewRelic PHP agent methods are not found.
     *
     * @param bool $throw
     * @param \Intouch\Newrelic\Handler\Handler|null $handler
     *
     */
    public function __construct($throw = false, Handler $handler = null)
    {
        $this->installed = extension_loaded('newrelic') && function_exists('newrelic_set_appname');

        if ($throw && !$this->installed) {
            throw new \RuntimeException('NewRelic PHP Agent does not appear to be installed');
        }

        if ($handler === null) {
            $handler = $this->installed ? new DefaultHandler() : new NullHandler();
        }

        $this->handler = $handler;
    }

    /**
     * Distributed tracing allows you to see the path that a request takes as it travels through a distributed system.
     * When distributed tracing is enabled, use newrelic_accept_distributed_trace_headers to accept a payload of
     * headers. These include both W3C Trace Context and New Relic distributed trace headers.
     *
     * It is possible to only accept only W3C Trace Context headers and disable the New Relic Distributed Tracing
     * header via the newrelic.distributed_tracing_exclude_newrelic_header INI setting.
     *
     * @param array $headers
     * @param string|null $transport_type
     * @return bool
     */
    public function acceptDistributedTraceHeaders(array $headers, string $transport_type = null): ?bool
    {
        return $this->call('newrelic_accept_distributed_trace_headers', [$headers, $transport_type]);
    }

    /**
     * @deprecated
     */
    public function acceptDistributedTracePayload(string $payload): void
    {
    }

    /**
     * @deprecated
     */
    public function acceptDistributedTracePayloadHttpSafe(string $httpsafe_payload, string $transport_type = null): void
    {
    }

    /**
     * Add a custom attribute (a key and a value data pair) to the current span. (The call name is
     * newrelic_add_custom_span_parameter because "custom attributes" were previously called "custom parameters.") For
     * example, you can add a customer's full name from your customer database. This attribute appears in any span. You
     * can also query the Span for your custom attributes.
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    public function addCustomSpanParameter(string $key, $value): ?bool
    {
        return $this->call('newrelic_add_custom_span_parameter', [$key, $value]);
    }

    /**
     * Add a custom parameter to the current web transaction with the specified value. For example, you can add a
     * customer's full name from your customer database. This parameter is shown in any transaction trace that results
     * from this transaction.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function addCustomParameter(string $key, $value): ?bool
    {
        return $this->call('newrelic_add_custom_parameter', [$key, $value]);
    }

    /**
     * API equivalent of the newrelic.transaction_tracer.custom setting. It allows you to add user defined functions or
     * methods to the list to be instrumented. Internal PHP functions cannot have custom tracing.
     *
     * NOTE: $function_name should be in the format 'foo' or 'Foo::Bar' as a string, not the PHP callable array format.
     *
     * @param string $functionName
     *
     * @return bool
     */
    public function addCustomTracer(string $function_name): ?bool
    {
        return $this->call('newrelic_add_custom_tracer', [$function_name]);
    }

    /**
     * If no argument or true as an argument is given, mark the current transaction as a background job. If false is
     * passed as an argument, mark the transaction as a web transaction.
     *
     * @param bool $flag
     *
     * @return mixed
     */
    public function backgroundJob(bool $flag = true): void
    {
        $this->call('newrelic_background_job', [$flag]);
    }

    /**
     * If enable is omitted or set to on, enables the capturing of URL parameters for displaying in transaction traces.
     * In essence this overrides the newrelic.capture_params setting. In agents prior to 2.1.3 this was called
     * newrelic_enable_params() but that name is now deprecated.
     *
     * @param bool $enable
     *
     * @return mixed
     */
    public function captureParams(bool $enable = true): void
    {
        $this->call('newrelic_capture_params', [$enable]);
    }

    /**
     * @deprecated
     */
    public function createDistributedTracePayload(): void
    {
    }

    /**
     * Adds a custom metric with the specified name and value, which is of type double. Values saved are assumed to be
     * milliseconds, so "4" will be stored as ".004" in our system. Your custom metrics can then be used in custom
     * dashboards and custom views in the New Relic user interface. It's a best practice to name your custom metrics
     * with a Custom/ prefix. This will make them easily usable in custom dashboards.
     *
     * Note: Avoid creating too many unique custom metric names. New Relic limits the total number of custom metrics you
     * can use (not the total you can report for each of these custom metrics). Exceeding more than 2000 unique custom
     * metric names can cause automatic clamps that will affect other data.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    public function customMetric(string $name, $value): ?bool
    {
        return $this->call('newrelic_custom_metric', [$name, $value]);
    }

    /**
     * Prevents the output filter from attempting to insert RUM JavaScript for this current transaction. Useful for
     * AJAX calls, for example.
     *
     * @return mixed
     */
    public function disableAutoRUM(): ?bool
    {
        return $this->call('newrelic_disable_autorum');
    }

    /**
     * Stop recording the web transaction immediately. Usually used when a page is done with all computation and is
     * about to stream data (file download, audio or video streaming etc) and you don't want the time taken to stream to
     * be counted as part of the transaction. This is especially relevant when the time taken to complete the operation
     * is completely outside the bounds of your application. For example, a user on a very slow connection may take a
     * very long time to download even small files, and you wouldn't want that download time to skew the real
     * transaction time.
     *
     * @return mixed
     */
    public function endOfTransaction(): void
    {
        $this->call('newrelic_end_of_transaction');
    }

    /**
     * Despite being similar in name to newrelic_end_of_transaction above, this call serves a very different purpose.
     * newrelic_end_of_transaction simply marks the end time of the transaction but takes no other action. The
     * transaction is still only sent to the daemon when the PHP engine determines that the script is done executing and
     * is shutting down. This function on the other hand, causes the current transaction to end immediately, and will
     * ship all of the metrics gathered thus far to the daemon unless the ignore parameter is set to true. In effect
     * this call simulates what would happen when PHP terminates the current transaction. This is most commonly used in
     * command line scripts that do some form of job queue processing. You would use this call at the end of processing
     * a single job task, and begin a new transaction (see below) when a new task is pulled off the queue.
     *
     * Normally, when you end a transaction you want the metrics that have been gathered thus far to be recorded.
     * However, there are times when you may want to end a transaction without doing so. In this case use the second
     * form of the function and set ignore to true.
     *
     * @param bool $ignore
     *
     * @return mixed
     */
    public function endTransaction(bool $ignore = false): ?bool
    {
        return $this->call('newrelic_end_transaction', [$ignore]);
    }


    /**
     * Returns the JavaScript string to inject as part of the header for browser timing (real user monitoring). If flag
     * is specified it must be a boolean, and if omitted, defaults to true. This indicates whether or not surrounding
     * script tags should be returned as part of the string.
     *
     * @param bool $include_tags
     *
     * @return string
     */
    public function getBrowserTimingHeader(bool $include_tags = true): string
    {
        return $this->call('newrelic_get_browser_timing_header', [$include_tags]);
    }

    /**
     * Returns the JavaScript string to inject at the very end of the HTML output for browser timing (real user
     * monitoring). If flag is specified it must be a boolean, and if omitted, defaults to true. This indicates whether
     * or not surrounding script tags should be returned as part of the string.
     *
     * @param bool $include_tags
     *
     * @return string
     */
    public function getBrowserTimingFooter(bool $include_tags = true): string
    {
        return $this->call('newrelic_get_browser_timing_footer', [$include_tags]);
    }

    /**
     * This call returns an opaque map of key-value pairs that can be used to correlate this application to other data
     * in the New Relic backend.
     *
     * @return mixed
     */
    public function getLinkingMetadata()
    {
        return $this->call('newrelic_get_linking_metadata');
    }

    /**
     * Returns an associative array containing the identifiers of the current trace and the parent span. This
     * information is useful for integrating with third party distributed tracing tools, such as Zipkin.
     *
     * @return array
     */
    public function getTraceMetadata(): array
    {
        return $this->call('newrelic_get_trace_metadata');
    }

    /**
     * Do not generate Apdex metrics for this transaction. This is useful when you have either very short or very long
     * transactions (such as file downloads) that can skew your apdex score.
     *
     * @return mixed
     */
    public function ignoreApdex(): void
    {
        $this->call('newrelic_ignore_apdex');
    }

    /**
     * Do not generate metrics for this transaction. This is useful when you have transactions that are particularly
     * slow for known reasons and you do not want them always being reported as the transaction trace or skewing your
     * site averages.
     *
     * @return mixed
     */
    public function ignoreTransaction(): void
    {
        $this->call('newrelic_ignore_transaction');
    }

    /**
     * Use newrelic_insert_distributed_trace_headers to manually add distributed tracing headers an array of outbound
     * headers.
     *
     * When Distributed Tracing is enabled, newrelic_insert_distributed_trace_headers will always insert W3C trace
     * context headers. It also, by default, inserts the New Relic Distributed Tracing header, but this can be disabled
     * via the newrelic.distributed_tracing_exclude_newrelic_header INI setting.
     *
     * @param array $headers
     * @return bool
     */
    public function insertDistributedTraceHeaders(array $headers): ?bool
    {
        return $this->call('newrelic_insert_distributed_trace_headers', [$headers]);
    }

    /**
     * Returns a value indicating whether or not the current transaction is marked as sampled.
     *
     * @return bool
     */
    public function isSampled(): ?bool
    {
        return $this->call('newrelic_is_sampled');
    }

    /**
     * Sets the name of the transaction to the specified string. This can be useful if you have implemented your own
     * dispatching scheme and wish to name transactions according to their purpose rather than their URL.
     *
     * Avoid creating too many unique transaction names. For example, if you have /product/123 and /product/234, if you
     * generate a separate transaction name for each, then New Relic will store separate information for these two
     * transaction names. This will make your graphs less useful, and may run into limits we set on the number of unique
     * transaction names per account. It also can slow down the performance of your application. Instead, store the
     * transaction as /product/*, or use something significant about the code itself to name the transaction, such as
     * /Product/view. The limit for the total number of transactions should be less than 1000 unique transaction
     * names -- exceeding that is not recommended.
     *
     * @param string $name
     *
     * @return bool
     */
    public function nameTransaction(string $name): ?bool
    {
        return $this->call('newrelic_name_transaction', [$name]);
    }

    /**
     * The PHP agent handles PHP errors and exceptions automatically for supported frameworks.
     * If you want to collect errors that are not handled automatically so that you can query for those errors in New
     * Relic and view error traces, you can use newrelic_notice_error.
     * If you want to use your own error and exception handlers, you can set newrelic_notice_error as the callback.
     *
     * NOTE: You should always pass an exception here if possible.
     *
     * @param mixed ...$params
     * @return void
     */
    public function noticeError(...$params): void
    {
        $this->call('newrelic_notice_error', $params);
    }

    /**
     * @param string $message
     */
    public function noticeErrorWithMessage(string $message): void
    {
        $this->noticeError($message);
    }

    /**
     * @param \Throwable $throwable
     */
    public function noticeErrorWithException(\Throwable $throwable): void
    {
        $this->noticeError($throwable);
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string|null $errfile
     * @param int|null $errline
     * @param string|null $errcontext
     */
    public function noticeErrorWithDetails(
        int $errno,
        string $errstr,
        string $errfile = null,
        int $errline = null,
        string $errcontext = null
    ): void {
        $this->noticeError($errno, $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * Records a New Relic Insights
     * {@link https://docs.newrelic.com/docs/insights/new-relic-insights/understanding-insights/new-relic-insights}
     * custom event.
     *
     * For more information, see Inserting custom events with the PHP agent
     * {@link https://docs.newrelic.com/docs/insights/new-relic-insights/adding-querying-data/inserting-custom-events-new-relic-apm-agents#php-att}.
     *
     * The attributes parameter is expected to be an associative array: the keys should be the attribute names
     * (which may be up to 255 characters in length), and the values should be scalar values: arrays and objects are
     * not supported.
     *
     * @param string $name
     * @param array $attributes
     *
     * @return mixed
     */
    public function recordCustomEvent(string $name, array $attributes): void
    {
        $this->call('newrelic_record_custom_event', [$name, $attributes]);
    }

    /**
     * Records a datastore segment. Datastore segments appear in the Breakdown table and Databases tab of the
     * Transactions page in the New Relic UI. This function allows an unsupported datastore to be instrumented in the
     * same way as the PHP agent automatically instruments its supported datastores.
     *
     * @param callable $func
     * @param array $parameters
     *
     * @return mixed
     */
    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        $this->call('newrelic_record_datastore_segment', [$func, $parameters]);
    }

    /**
     * Sets the name of the application to name. The string uses the same format as newrelic.appname and can set
     * multiple application names by separating each with a semi-colon. However please be aware of the restriction on
     * the application name ordering as described for that setting.
     *
     * The first application name is the primary name, and up to two extra application names can be specified (however
     * the same application name can only ever be used once as a primary name). This function should be called as early
     * as possible, and will have no effect if called after the RUM footer has been sent. You may want to consider
     * setting the application name in a file loaded by PHP's auto_prepend_file INI setting. This function returns true
     * if it succeeded or false otherwise.
     *
     * If you use multiple licenses you can also specify a license key along with the application name. An application
     * can appear in more than one account and the license key controls which account you are changing the name in.
     * If you do not wish to change the license and wish to use the third variant, simply set the license key to the
     * empty string ("").
     *
     * The xmit flag is new in version 3.1 of the agent. Usually, when you change an application name, the agent simply
     * discards the current transaction and does not send any of the accumulated metrics to the daemon. However, if you
     * want to record the metric and transaction data up to the point at which you called this function, you can specify
     * a value of true for this argument to make the agent send the transaction to the daemon. This has a very slight
     * performance impact as it takes a few milliseconds for the agent to dump its data. By default this parameter is
     * false.
     *
     * @param string $name
     * @param string|null $license
     * @param bool $xmit
     *
     * @return bool
     */
    public function setAppName(string $name, string $license = null, bool $xmit = null): ?bool
    {
        return $this->call('newrelic_set_appname', [$name, $license, $xmit]);
    }

    /**
     * Adds the three parameter strings to collected browser traces. All three parameters are required, but may be empty
     * strings. For more information please see the section on
     * {@link https://newrelic.com/docs/features/browser-traces browser traces}.
     *
     * @param string $user
     * @param string $account
     * @param string $product
     *
     * @return bool
     */
    public function setUserAttributes(string $user = "", string $account = "", string $product = ""): ?bool
    {
        return $this->call('newrelic_set_user_attributes', [$user, $account, $product]);
    }

    /**
     * If you have ended a transaction before your script terminates (perhaps due to it just having finished a task in a
     * job queue manager) and you want to start a new transaction, use this call. This will perform the same operations
     * that occur when the script was first started. Of the two arguments, only the application name is mandatory.
     * However, if you are processing tasks for multiple accounts, you may also provide a license for the associated
     * account. The license set for this API call will supersede all per-directory and global default licenses
     * configured in INI files.
     *
     * @param string $name
     * @param string|null $license
     *
     * @return bool
     */
    public function startTransaction(string $name, string $license = null): ?bool
    {
        return $this->call('newrelic_start_transaction', [$name, $license]);
    }

    /**
     * Call the named method with the given params.  Return false if the NewRelic PHP agent is not available.
     *
     * @param string $method
     * @param array $params
     *
     * @return mixed
     */
    protected function call(string $method, array $params = [])
    {
        return $this->handler->handle($method, $params);
    }
}
