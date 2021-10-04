        The following endpoints are currently available:

### GET `/api/versions`

Returns all PHP versions 5.6+

**Sample Response**

  ```json
    [{
      "id": 1,
      "major": 8,
      "minor": 0,
      "release": 10,
      "tagged_at": "2021-08-24T17:48:14.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "created_at": "2021-09-10T17:53:23.000000Z",
      "updated_at": "2021-09-10T17:53:23.000000Z",
      "needs_patch": false,
      "needs_upgrade": false
    },
    {
      "id": 4,
      "major": 8,
      "minor": 0,
      "release": 9,
      "tagged_at": "2021-07-29T14:58:35.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "created_at": "2021-09-10T17:53:23.000000Z",
      "updated_at": "2021-09-10T17:53:23.000000Z",
      "needs_patch": true,
      "needs_upgrade": false
    },
    {
      "id": 6,
      "major": 8,
      "minor": 0,
      "release": 8,
      "tagged_at": "2021-06-29T09:56:31.000000Z",
      "active_support_until": "2022-11-26T18:03:09.000000Z",
      "security_support_until": "2023-11-26T18:03:09.000000Z",
      "created_at": "2021-09-10T17:53:23.000000Z",
      "updated_at": "2021-09-10T17:53:23.000000Z",
      "needs_patch": true,
      "needs_upgrade": false
    }, ...]
```

### GET `/api/versions/:version`

Returns information for the major/minor/release level version requested. For specific releases (ex: 8.0.10), you will receive additional data for `needs_patch`, `needs_upgrade`, and `latest_release`.

**Sample Response**

  ```json
  {
    "provided": {
      "id": 85,
      "major": 7,
      "minor": 3,
      "release": 3,
      "tagged_at": "2019-03-05T13:49:42.000000Z",
      "active_support_until": "2020-12-06T16:08:24.000000Z",
      "security_support_until": "2021-12-06T16:08:24.000000Z",
      "created_at": "2021-09-10T17:53:24.000000Z",
      "updated_at": "2021-09-10T17:53:24.000000Z",
      "needs_patch": true,
      "needs_upgrade": true
    },
    "latest_release": "8.0.10"
  }
```

### GET `/api/versions/latest`

Returns a string value of the latest release of the highest major version

**Sample Response**

  ```
  "8.0.10"
```

### GET `/api/versions/minimum-supported/:support-type`

Takes a string `active` or `security` and returns the minimum version supported

**Sample Response**

  ```json
  {
    "id": 2,
    "major": 7,
    "minor": 4,
    "release": 23,
    "tagged_at": "2021-08-24T17:35:21.000000Z",
    "active_support_until": "2021-11-28T20:46:01.000000Z",
    "security_support_until": "2022-11-28T20:46:01.000000Z",
    "created_at": "2021-09-10T17:53:23.000000Z",
    "updated_at": "2021-09-10T17:53:23.000000Z",
    "needs_patch": false,
    "needs_upgrade": false
  }
```
