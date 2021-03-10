# Changelog

All notable changes to `laravel-mailcoach-sendgrid-feedback` will be documented in this file

## 3.0.0 - 2021-03-10

- Support for Mailcoach v4

## 2.3.1 - 2020-09-25

- Fix check on first message id of json payload

## 2.3.0 - 2020-09-24

- Tag a Mailcoach v3 compatible release

## 2.2.7 - 2020-09-09

- fix Laravel 8 support

## 2.2.6 - 2020-09-09

- add support for Laravel 8

## 2.2.5 - 2020-09-03

- Don't handle temporary bounces as permanent bounces

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
