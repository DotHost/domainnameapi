## Installation and Integration Guide

[![Better Stack Badge](https://uptime.betterstack.com/status-badges/v2/monitor/1mhpy.svg)](https://uptime.betterstack.com/?utm_source=status_badge)

### Minimum Requirements

- PHP 8.1 or higher
- PHP SOAPClient extension must be active.

## Usage

Check out the documentation here [Documentation](https://documenter.getpostman.com/view/28163278/2sAY4sjjoF)

For username and password, create an account on [DomainNameAPI](https://www.domainnameapi.com/become-a-reseller) website

## Deployment

You can use our live link here [DotHostDomainAPI](https://domain.hostwithdothost.com/)

```
Rate Limit:
- Maximum of 1000 requests
- 10-minute window
```

or you can download the files and self host on a cPanel or VPS server

```

## Configuration

- Change the default nameserver in the /DomainNameAPI directory
- function of RegisterWithContactInfo


## Response and Error Codes with Explanations

| Code | Description                                     | Detail                                                                                  |
| ---- | ----------------------------------------------- | --------------------------------------------------------------------------------------- |
| 1000 | Command completed successfully                  | Operation successful.                                                                   |
| 1001 | Command completed successfully; action pending. | Operation successful, but it's queued for completion.                                   |
| 2003 | Required parameter missing                      | Parameter missing error. For example, missing phone entry in contact info.              |
| 2105 | Object is not eligible for renewal              | Domain status not eligible for renewal; it's locked for updates.                        |
| 2200 | Authentication error                            | Authorization error, security code incorrect, or domain with another registrar.         |
| 2302 | Object exists                                   | Domain or nameserver info already exists in the database.                               |
| 2303 | Object does not exist                           | Domain or nameserver info does not exist in the database. A new record must be created. |
| 2304 | Object status prohibits operation               | Domain status not eligible for updates, it's locked for updates.                        |
```
