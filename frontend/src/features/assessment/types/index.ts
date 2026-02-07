/**
 * Assessment Domain Types
 * 
 * ⚠️ IMPORTANT: This domain has TWO separate status flows:
 * 
 * 1. Assessment (Parent) Status Flow:
 *    draft → active → submitted → reviewed → pending_finish → finished
 *    Alternative: rejected, cancelled
 *    See: assessment.types.ts
 * 
 * 2. Assessment Response (Requirement) Status Flow:
 *    active → pending_review → reviewed
 *    Alternative: cancelled
 *    See: assessment-response.types.ts
 * 
 * Never mix these two status enums!
 */

// Assessment types (parent entity)
export * from "./assessment.types";

// Assessment Response types (individual requirements)
export * from "./assessment-response.types";
