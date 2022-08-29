The following endpoints are currently available, along with the optional `/versions` route alias:

### GET `/api/releases`

Returns all PHP releases 5.6+.

**Sample Response**

  ```json
    [{
      "major": 8,
      "minor": 0,
      "release": 10,
      "tagged_at": "2021-08-24T17:48:14.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "needs_patch": false,
      "needs_upgrade": false,
      "changelog_url": "https://www.php.net/ChangeLog-8.php#8.0.10"
    },
    {
      "major": 8,
      "minor": 0,
      "release": 9,
      "tagged_at": "2021-07-29T14:58:35.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "needs_patch": true,
      "needs_upgrade": false,
      "changelog_url": "https://www.php.net/ChangeLog-8.php#8.0.9"
    },
    {
      "major": 8,
      "minor": 0,
      "release": 8,
      "tagged_at": "2021-06-29T09:56:31.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "needs_patch": true,
      "needs_upgrade": false, 
      "changelog_url": "https://www.php.net/ChangeLog-8.php#8.0.8"
    }, ...]
```

### GET `/api/releases/:release`

Takes string (ex: "7", "7.2", "7.2.12") and returns information for the major/minor/release level version requested. For specific releases (ex: 8.0.10), you will additionally receive PHP's `latest_release` number.

**Sample Response**

  ```json
  {
    "provided": {
      "major": 7,
      "minor": 3,
      "release": 3,
      "tagged_at": "2019-03-05T13:49:42.000000Z",
      "active_support_until": "2020-12-06T16:08:24.000000Z",
      "security_support_until": "2021-12-06T16:08:24.000000Z",
      "needs_patch": true,
      "needs_upgrade": true,
      "changelog_url": "https://www.php.net/ChangeLog-7.php#7.3.3"
    },
    "latest_release": "8.0.10"
  }
```

### GET `/api/releases/latest`

Returns a string value of the latest release of the highest major version.

**Sample Response**

  ```
  "8.0.10"
```

### GET `/api/releases/minimum-supported/:support-type`

Takes a string `active` or `security` and returns the minimum release supported.

**Sample Response**

  ```json
  {
    "major": 7,
    "minor": 4,
    "release": 23,
    "tagged_at": "2021-08-24T17:35:21.000000Z",
    "active_support_until": "2021-11-28T20:46:01.000000Z",
    "security_support_until": "2022-11-28T20:46:01.000000Z",
    "needs_patch": false,
    "needs_upgrade": false,
    "changelog_url": "https://www.php.net/ChangeLog-7.php#7.4.23"
  }
```
