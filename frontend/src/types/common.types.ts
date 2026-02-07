// Common utility types

export type Maybe<T> = T | null
export type Nullable<T> = T | null | undefined

export interface SelectOption<T = string> {
  label: string
  value: T
}

export type SortOrder = 'asc' | 'desc'

export interface DateRange {
  from: Date
  to: Date
}
