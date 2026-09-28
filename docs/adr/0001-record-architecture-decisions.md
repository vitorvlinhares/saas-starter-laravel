# 1. Record architecture decisions

## Status

Accepted

## Context

This project makes a handful of decisions (tenancy model, billing entity, authorization strategy) that are easy to
second-guess or accidentally reverse in a later refactor if the reasoning isn't written down anywhere.

## Decision

We use Architecture Decision Records (ADRs), as described by Michael Nygard, to capture significant architectural
decisions, their context, and their consequences. Each ADR is a short markdown file in `docs/adr/`, numbered
sequentially, and is not rewritten once accepted — a later decision that changes course gets its own ADR that
supersedes the earlier one.

## Consequences

Anyone changing tenancy, billing, or authorization should read the relevant ADR first, and add a new one if the
decision changes.
