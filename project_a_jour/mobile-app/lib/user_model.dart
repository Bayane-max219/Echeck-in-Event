class User {
  final int id;
  final String email;
  final String firstName;
  final String lastName;
  final List<String> roles;
  final String createdAt;
  final bool isConfirmed;

  User({
    required this.id,
    required this.email,
    required this.firstName,
    required this.lastName,
    required this.roles,
    required this.createdAt,
    required this.isConfirmed,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      email: json['email'] ?? '-',
      firstName: json['firstName'] ?? '-',
      lastName: json['lastName'] ?? '-',
      roles: (json['roles'] as List?)?.map((e) => e.toString()).toList() ?? [],
      createdAt: json['createdAt'] ?? '-',
      isConfirmed: json['is_confirmed'] == true || json['isConfirmed'] == true,
    );
  }
}
