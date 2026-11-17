# User and residency design

## One user model

Every person who can sign in is a share-house resident. Administrators also
participate as residents, so authentication and residency are represented by
the same `users` table instead of separate `users` and `residents` tables.

The three concerns remain independent:

- `role` controls authorization (`admin` or `resident`).
- `move_in_date` and `move_out_date` describe the residency period.
- Rotation participation will be represented by a separate membership table in
  a later Issue because joining a duty rotation is different from living in the
  house.

## Residency dates

`move_in_date` is required. `move_out_date` is nullable while the person still
lives in the house. Both boundary dates are inclusive: a person is a resident
on both their move-in date and their move-out date.

Date fields represent Japanese calendar dates and do not contain a timezone.
Code that asks whether a person is currently resident must first obtain today's
date in `Asia/Tokyo`, then pass that date to `User::isResidentOn()`.

## Roles and protected attributes

The PHP `UserRole` enum gives application code a type-safe role value. The
MySQL enum independently rejects unknown values written outside the Laravel
application. A test compares both definitions so changes cannot silently make
them inconsistent.

`role`, `move_in_date`, and `move_out_date` are not mass assignable. They affect
authorization or account availability and must be assigned explicitly by a
trusted use case. Strict Eloquent behavior is enabled outside production so an
accidental mass assignment fails visibly during development and tests.

## Commands, seeders, and factories

- `app:create-user` creates a real local or operational account through hidden
  password prompts and validation.
- `DatabaseSeeder` must not create an account with publicly known credentials.
- `UserFactory` creates test data and may bypass mass-assignment protection as
  part of controlled test setup.
