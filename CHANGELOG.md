# Changelog

All notable changes to `laravel-mailcoach-sendgrid-feedback` will be documented in this file

## 2.2.4 - 2020-08-13

- Don't handle same sg_event_ids twice - (#10)
- Only handle payloads that are for the relevant subscriber - (#11)

## 2.2.3 - 2020-07-24

- handle events without related send - fixes (#7)


## 2.2.2 - 2020-07-20

- validate webhook event - fixes TypeError (#9)

## 2.2.1 - 2020-07-06

- Use the configured app timezone for stored feedback

## 2.2.0 - 2020-04-27

- fire `WebhookCallProcessedEvent` when processing webhook is complete

## 2.1.1 - 2020-04-09

- fix time on feedback registration

## 2.1.0 - 2020-03-20

- add ability to use a custom queue connection

## 2.0.0 - 2020-03-10

- add support for Laravel 7 and Mailcoach v2

## 1.0.0 - 2020-01-29

- initial release
