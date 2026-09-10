---
paths:
  - 'resources/views/components/promotions/**'
---

# Promotions

## Promotion eligibility: officers (Captain+) recommend, admins approve
Promotions page (/promotions) is accessible to all authenticated users. Only admins can approve/reject; recommendation submission is allowed for admins OR any soldier whose rank level is >= 12 (Captain and above) — see canRecommend() computed property. Soldiers cannot recommend themselves (validation closure on personnel_id). The promotions.remarks column is NOT NULL, so storePromotion defaults remarks to '' when omitted. recommend_by tracks the recommender (shown in the review modal).
