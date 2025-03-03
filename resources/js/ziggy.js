const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"cashier.payment":{"uri":"stripe\/payment\/{id}","methods":["GET","HEAD"],"parameters":["id"]},"cashier.webhook":{"uri":"stripe\/webhook","methods":["POST"]},"sanctum.csrf-cookie":{"uri":"sanctum\/csrf-cookie","methods":["GET","HEAD"]},"subjects.index":{"uri":"api\/subjects","methods":["GET","HEAD"]},"subjects.store":{"uri":"api\/subjects","methods":["POST"]},"subjects.show":{"uri":"api\/subjects\/{subject}","methods":["GET","HEAD"],"parameters":["subject"],"bindings":{"subject":"id"}},"subjects.update":{"uri":"api\/subjects\/{subject}","methods":["PUT","PATCH"],"parameters":["subject"],"bindings":{"subject":"id"}},"subjects.destroy":{"uri":"api\/subjects\/{subject}","methods":["DELETE"],"parameters":["subject"],"bindings":{"subject":"id"}},"classes.index":{"uri":"api\/classes","methods":["GET","HEAD"]},"classes.store":{"uri":"api\/classes","methods":["POST"]},"classes.show":{"uri":"api\/classes\/{class}","methods":["GET","HEAD"],"parameters":["class"],"bindings":{"class":"id"}},"classes.update":{"uri":"api\/classes\/{class}","methods":["PUT","PATCH"],"parameters":["class"],"bindings":{"class":"id"}},"classes.destroy":{"uri":"api\/classes\/{class}","methods":["DELETE"],"parameters":["class"],"bindings":{"class":"id"}},"students.index":{"uri":"api\/students","methods":["GET","HEAD"]},"students.store":{"uri":"api\/students","methods":["POST"]},"students.show":{"uri":"api\/students\/{student}","methods":["GET","HEAD"],"parameters":["student"],"bindings":{"student":"id"}},"students.update":{"uri":"api\/students\/{student}","methods":["PUT","PATCH"],"parameters":["student"],"bindings":{"student":"id"}},"students.destroy":{"uri":"api\/students\/{student}","methods":["DELETE"],"parameters":["student"],"bindings":{"student":"id"}},"attendances.index":{"uri":"api\/attendances","methods":["GET","HEAD"]},"attendances.store":{"uri":"api\/attendances","methods":["POST"]},"attendances.show":{"uri":"api\/attendances\/{attendance}","methods":["GET","HEAD"],"parameters":["attendance"],"bindings":{"attendance":"id"}},"attendances.update":{"uri":"api\/attendances\/{attendance}","methods":["PUT","PATCH"],"parameters":["attendance"],"bindings":{"attendance":"id"}},"attendances.destroy":{"uri":"api\/attendances\/{attendance}","methods":["DELETE"],"parameters":["attendance"],"bindings":{"attendance":"id"}},"exams.index":{"uri":"api\/exams","methods":["GET","HEAD"]},"exams.store":{"uri":"api\/exams","methods":["POST"]},"exams.show":{"uri":"api\/exams\/{exam}","methods":["GET","HEAD"],"parameters":["exam"],"bindings":{"exam":"id"}},"exams.update":{"uri":"api\/exams\/{exam}","methods":["PUT","PATCH"],"parameters":["exam"],"bindings":{"exam":"id"}},"exams.destroy":{"uri":"api\/exams\/{exam}","methods":["DELETE"],"parameters":["exam"],"bindings":{"exam":"id"}},"report-cards.batch-print":{"uri":"api\/report-cards\/batch-print","methods":["GET","HEAD"]},"report-cards.index":{"uri":"api\/report-cards","methods":["GET","HEAD"]},"report-cards.store":{"uri":"api\/report-cards","methods":["POST"]},"report-cards.update":{"uri":"api\/report-cards\/{report_card}","methods":["PUT","PATCH"],"parameters":["report_card"]},"report-cards.destroy":{"uri":"api\/report-cards\/{report_card}","methods":["DELETE"],"parameters":["report_card"]},"report-cards.bulk.store":{"uri":"api\/report-cards\/bulk","methods":["POST"]},"report-cards.show":{"uri":"api\/report-cards\/{report_card}","methods":["GET","HEAD"],"wheres":{"report_card":"[0-9]+"},"parameters":["report_card"]},"home":{"uri":"\/","methods":["GET","HEAD"]},"admin.redirect":{"uri":"admin-redirect","methods":["GET","HEAD"]},"dashboard":{"uri":"dashboard","methods":["GET","HEAD"]},"admin.tenants.index":{"uri":"admin\/tenants","methods":["GET","HEAD"]},"admin.tenants.create":{"uri":"admin\/tenants\/create","methods":["GET","HEAD"]},"admin.tenants.store":{"uri":"admin\/tenants","methods":["POST"]},"admin.tenants.show":{"uri":"admin\/tenants\/{tenant}","methods":["GET","HEAD"],"parameters":["tenant"]},"admin.tenants.edit":{"uri":"admin\/tenants\/{tenant}\/edit","methods":["GET","HEAD"],"parameters":["tenant"]},"admin.tenants.update":{"uri":"admin\/tenants\/{tenant}","methods":["PUT","PATCH"],"parameters":["tenant"],"bindings":{"tenant":"id"}},"admin.tenants.destroy":{"uri":"admin\/tenants\/{tenant}","methods":["DELETE"],"parameters":["tenant"],"bindings":{"tenant":"id"}},"admin.subscriptions.index":{"uri":"admin\/subscriptions","methods":["GET","HEAD"]},"admin.subscriptions.create":{"uri":"admin\/subscriptions\/create","methods":["GET","HEAD"]},"admin.subscriptions.store":{"uri":"admin\/subscriptions","methods":["POST"]},"admin.subscriptions.show":{"uri":"admin\/subscriptions\/{subscription}","methods":["GET","HEAD"],"parameters":["subscription"]},"admin.subscriptions.edit":{"uri":"admin\/subscriptions\/{subscription}\/edit","methods":["GET","HEAD"],"parameters":["subscription"]},"admin.subscriptions.update":{"uri":"admin\/subscriptions\/{subscription}","methods":["PUT","PATCH"],"parameters":["subscription"]},"admin.subscriptions.destroy":{"uri":"admin\/subscriptions\/{subscription}","methods":["DELETE"],"parameters":["subscription"]},"admin.plans.index":{"uri":"admin\/plans","methods":["GET","HEAD"]},"admin.plans.create":{"uri":"admin\/plans\/create","methods":["GET","HEAD"]},"admin.plans.store":{"uri":"admin\/plans","methods":["POST"]},"admin.plans.edit":{"uri":"admin\/plans\/{plan}\/edit","methods":["GET","HEAD"],"parameters":["plan"],"bindings":{"plan":"id"}},"admin.plans.update":{"uri":"admin\/plans\/{plan}","methods":["PUT"],"parameters":["plan"],"bindings":{"plan":"id"}},"admin.plans.destroy":{"uri":"admin\/plans\/{plan}","methods":["DELETE"],"parameters":["plan"],"bindings":{"plan":"id"}},"admin.tenants.subscriptions.store":{"uri":"admin\/tenants\/{tenant}\/subscriptions","methods":["POST"],"parameters":["tenant"],"bindings":{"tenant":"id"}},"admin.subscriptions.cancel":{"uri":"admin\/subscriptions\/{subscription}\/cancel","methods":["POST"],"parameters":["subscription"]},"admin.subscriptions.renew":{"uri":"admin\/subscriptions\/{subscription}\/renew","methods":["POST"],"parameters":["subscription"]},"admin.subscriptions.reports":{"uri":"admin\/subscriptions\/reports","methods":["GET","HEAD"]},"tenant.students":{"uri":"students","methods":["GET","HEAD"]},"tenant.teachers":{"uri":"teachers","methods":["GET","HEAD"]},"tenant.academics":{"uri":"academics","methods":["GET","HEAD"]},"tenant.classes":{"uri":"classes","methods":["GET","HEAD"]},"tenant.attendance":{"uri":"attendance","methods":["GET","HEAD"]},"tenant.attendance.admin":{"uri":"attendance-admin","methods":["GET","HEAD"]},"tenant.report.cards":{"uri":"report-cards","methods":["GET","HEAD"]},"tenant.exams":{"uri":"exams","methods":["GET","HEAD"]},"tenant.subjects":{"uri":"subjects","methods":["GET","HEAD"]},"manage-subject":{"uri":"manage-subject\/{id?}","methods":["GET","HEAD"],"parameters":["id"]},"batch-print-report-cards":{"uri":"batch-print-report-cards","methods":["GET","HEAD"]},"tenant.bulk.report.cards":{"uri":"bulk-report-cards","methods":["GET","HEAD"]},"settings.school":{"uri":"settings\/school","methods":["GET","HEAD"]},"settings.school.update":{"uri":"settings\/school","methods":["PUT"]},"profile.edit":{"uri":"settings\/profile","methods":["GET","HEAD"]},"profile.update":{"uri":"settings\/profile","methods":["PATCH"]},"profile.destroy":{"uri":"settings\/profile","methods":["DELETE"]},"password.edit":{"uri":"settings\/password","methods":["GET","HEAD"]},"password.update":{"uri":"settings\/password","methods":["PUT"]},"appearance":{"uri":"settings\/appearance","methods":["GET","HEAD"]},"tenant-management":{"uri":"settings\/tenant-management","methods":["GET","HEAD"]},"tenant-management.tenants":{"uri":"settings\/tenant-management\/tenants","methods":["GET","HEAD"]},"tenant-management.tenants-for-admin":{"uri":"settings\/tenant-management\/tenants-for-admin","methods":["GET","HEAD"]},"register":{"uri":"register","methods":["GET","HEAD"]},"login":{"uri":"login","methods":["GET","HEAD"]},"password.request":{"uri":"forgot-password","methods":["GET","HEAD"]},"password.email":{"uri":"forgot-password","methods":["POST"]},"password.reset":{"uri":"reset-password\/{token}","methods":["GET","HEAD"],"parameters":["token"]},"password.store":{"uri":"reset-password","methods":["POST"]},"verification.notice":{"uri":"verify-email","methods":["GET","HEAD"]},"verification.verify":{"uri":"verify-email\/{id}\/{hash}","methods":["GET","HEAD"],"parameters":["id","hash"]},"verification.send":{"uri":"email\/verification-notification","methods":["POST"]},"password.confirm":{"uri":"confirm-password","methods":["GET","HEAD"]},"logout":{"uri":"logout","methods":["POST"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
