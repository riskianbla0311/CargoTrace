# CargoTrace – Safe CRUD Archive Notes

## Admin web actions

| Module | Admin action | Data behavior |
|---|---|---|
| Shipments | Archive | Soft delete; tracking, exceptions, items, and legs are retained |
| Carriers | Deactivate | Sets `is_active = false`; historical shipment references remain valid |
| Locations | Deactivate | Sets `is_active = false`; historical shipment and tracking references remain valid |
| Users | Deactivate | Sets `is_active = false`; user and audit history remain available |

## Safeguards

- All archive/deactivation routes are inside the Admin-only route group, except shipment archive which has explicit Admin middleware.
- User cannot deactivate their own account.
- The last active Admin cannot be deactivated.
- Inactive accounts are denied login through both web and API login.
- Inactive carriers and locations are excluded from active reference-data choices used for new shipments.
- Important actions create audit log entries.

## Apply the migration

1. Back up the current database.
2. Copy the updated project files over the current project (do not overwrite `.env`).
3. Run `php artisan migrate`.
4. Run `php artisan optimize:clear`.
5. Log in as Admin and verify Archive/Deactivate actions.

No `migrate:fresh` or database reset is required.
