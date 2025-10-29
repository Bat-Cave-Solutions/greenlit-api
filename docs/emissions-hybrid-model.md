# Emissions Hybrid Model Documentation

## Overview

The Greenlit emissions system uses a **hybrid data model** that combines relational columns for structured, frequently-queried data with a flexible JSONB column for category-specific inputs. This approach provides both performance and flexibility.

## Table Structure

### Core Relational Columns

The `emissions` table includes the following relational columns for optimal query performance:

- **Identifiers & References**:
  - `id` - Primary key
  - `production_id` - Foreign key to productions table
  - `emission_factor_id` - Foreign key to emission_factors table
  - `custom_factor_id` - Foreign key to custom_emission_factors table

- **Temporal Data**:
  - `record_date` - The actual date of the emission activity
  - `record_period` - YYYYMM format for aggregation (indexed)

- **Classification**:
  - `activity_code` - References activity_code_tree (indexed)
  - `scope` - Emission scope (1, 2, or 3) with CHECK constraint
  - `country` - ISO 3166-1 alpha-2 country code (two uppercase letters)
  - `department` - Optional department identifier

- **Calculation Results**:
  - `calculation_version` - Version of calculation engine used
  - `calculated_co2e` - Final CO2 equivalent result in kg
  - `record_flags` - Bit flags for audit trail and status

### JSONB Data Column

The `data` column stores category-specific inputs as JSONB, allowing:
- **Flexible schema** per emission category
- **Rich data capture** without schema migrations
- **Efficient querying** via GIN indexes
- **Future extensibility** for new emission categories

### Generated Columns

For frequently-accessed JSON keys, we promote them to generated columns:

```sql
-- Flight-specific generated columns
flight_origin VARCHAR(10) GENERATED ALWAYS AS (data->>'flight_origin') STORED,
flight_destination VARCHAR(10) GENERATED ALWAYS AS (data->>'flight_destination') STORED,
flight_distance_km DECIMAL(10,2) GENERATED ALWAYS AS (CAST(data->>'flight_distance_km' AS decimal(10,2))) STORED
```

Benefits of generated columns:
- **Automatic updates** when JSON data changes
- **Standard SQL indexing** on JSON-derived values
- **Type safety** with proper data type casting
- **Query optimization** - query planner can use these indexes

## JSON Key Promotion Policy

### When to Promote JSON Keys

Promote a JSON key to a generated column when it meets **2+ of these criteria**:

1. **High Query Frequency**: Used in WHERE clauses >50% of common queries
2. **Index Requirements**: Needs B-tree index for sorting/range queries
3. **Join Conditions**: Used to join with other tables
4. **Aggregation**: Frequently used in GROUP BY or aggregate functions
5. **Reporting**: Required for standard reports or dashboards
6. **Data Type Benefits**: Benefits from strong typing (dates, numbers)

### Current Promoted Keys

#### Flight Activities (`flight_*`)
- `flight_origin` - Airport code indexing for route analysis
- `flight_destination` - Airport code indexing for route analysis  
- `flight_distance_km` - Numeric operations and distance-based queries

#### Accommodation Activities (`accommodation_*`)
- *No current promotions* - Consider promoting `nights` if aggregation becomes common

#### Waste Activities (`waste_*`)
- *No current promotions* - Consider promoting `amount` for quantity analysis

### Future Promotion Candidates

Monitor usage patterns and consider promoting:
- `employee_id` - If employee-based reporting increases
- `trip_purpose` - If business purpose analysis becomes frequent
- `nights` - For accommodation duration analysis
- `waste_type` - For waste categorization reporting

## Database Constraints

### CHECK Constraints

Critical business rules enforced at database level:

```sql
-- Scope validation
CHECK (scope IN (1, 2, 3))

-- Period format validation  
CHECK (record_period >= 190001 AND record_period <= 999912)

-- Country code validation (alpha-2)
CHECK (LENGTH(country) = 2)

-- Factor requirement
CHECK (emission_factor_id IS NOT NULL OR custom_factor_id IS NOT NULL)

-- Category-specific data validation
CHECK (
  activity_code NOT LIKE 'flight_%' OR 
  (data ? 'flight_origin' AND data ? 'flight_destination')
)
```

### Indexes

#### B-tree Indexes (Relational Data)
```sql
-- Core query patterns
INDEX (production_id, record_date)
INDEX (production_id, record_period) 
INDEX (scope, country)
INDEX (activity_code)
INDEX (record_period)

-- Generated column indexes
INDEX (flight_origin)
INDEX (flight_destination)  
INDEX (flight_origin, flight_destination)
```

#### GIN Index (JSON Data)
```sql
-- JSONB containment and key existence
CREATE INDEX emissions_data_gin ON emissions USING GIN (data);
```

## Usage Examples

### Querying Generated Columns
```sql
-- Flight route analysis using generated columns
SELECT flight_origin, flight_destination, COUNT(*), SUM(calculated_co2e)
FROM emissions 
WHERE activity_code LIKE 'flight_%'
  AND record_period >= 202401
GROUP BY flight_origin, flight_destination
ORDER BY SUM(calculated_co2e) DESC;
```

### JSON Path Queries
```sql  
-- Query JSON data directly
SELECT data->>'employee_id', SUM(calculated_co2e)
FROM emissions
WHERE data ? 'employee_id'
  AND record_period = 202410
GROUP BY data->>'employee_id';
```

### Complex JSON Filtering
```sql
-- Multi-key JSON conditions
SELECT * FROM emissions
WHERE data @> '{"class": "business"}'::jsonb
  AND CAST(data->>'flight_distance_km' AS numeric) > 5000;
```

## Performance Considerations

1. **Query Planning**: PostgreSQL optimizer uses generated column indexes automatically
2. **Storage**: Generated columns use additional disk space but improve query speed
3. **Maintenance**: Generated columns update automatically on JSON changes
4. **Concurrency**: JSONB updates may require more locking than relational updates

## Migration Strategy

When promoting a new JSON key:

1. **Add generated column** with migration
2. **Create appropriate indexes**
3. **Update application queries** to use generated column
4. **Monitor performance** improvements
5. **Document** the promotion decision

## Best Practices

1. **Validate JSON early** - Use CHECK constraints for critical keys
2. **Index strategically** - Don't over-index generated columns  
3. **Monitor usage** - Track query patterns to identify promotion candidates
4. **Type consistently** - Use consistent data types in JSON values
5. **Document schemas** - Maintain JSON schema documentation per category